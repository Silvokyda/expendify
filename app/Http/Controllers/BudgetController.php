<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = $request->user()->budgets()->latest()->paginate(12);
        return view('app.budgets.index', compact('budgets'));
    }

    /** Step 1 (GET): name + timeframe + total. Also routes step=2/3 safely. */
    public function create(Request $request)
    {
        $step = (int) $request->query('step', 1);

        if ($step === 2) {
            if (!$request->session()->has('budget_wizard.step1')) {
                return redirect()
                    ->route('budgets.create')
                    ->with('status', 'Start by filling Step 1.');
            }
            return $this->step2($request);
        }

        if ($step === 3) {
            if (
                !$request->session()->has('budget_wizard.step1') ||
                !$request->session()->has('budget_wizard.items')
            ) {
                return redirect()
                    ->route('budgets.create')
                    ->with('status', 'Start by filling Step 1.');
            }
            return $this->review($request);
        }

        return view('app.budgets.create-step1');
    }

    /** POST Step 1 → store in session, go to Step 2 */
    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'period' => ['required', 'in:monthly,weekly,custom'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if ($data['period'] !== 'custom') {
            $data['start_date'] = null;
            $data['end_date'] = null;
        }

        $request->session()->put('budget_wizard.step1', $data);
        // clear later steps if user restarted
        $request->session()->forget('budget_wizard.items');

        return redirect()->route('budgets.create', ['step' => 2]);
    }

    /** GET Step 2 page */
    protected function step2(Request $request)
    {
        $user = $request->user();
        $categories = Category::forUser($user->id)
            ->orderBy('type')->orderBy('name')
            ->get(['id', 'name', 'type', 'icon', 'user_id']);

        $savingsGoals = method_exists($user, 'savingsGoals')
            ? $user->savingsGoals()->orderBy('name')->get(['id', 'name', 'target_amount'])
            : collect();

        $step1 = $request->session()->get('budget_wizard.step1');

        return view('app.budgets.create-step2', compact('categories', 'savingsGoals', 'step1'));
    }

    /** POST Step 2 → validate items, keep in session, go to Review */
    public function storeStep2(Request $request)
    {
        $step1 = $request->session()->get('budget_wizard.step1');
        if (!$step1) {
            return redirect()
                ->route('budgets.create')
                ->with('status', 'Your session for Step 1 expired. Please re-enter the basic budget info.');
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.kind' => ['required', 'in:category,goal,custom'],
            'items.*.type' => ['required', 'in:income,expense,saving'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.category_id' => ['nullable', 'exists:categories,id'],
            'items.*.savings_goal_id' => ['nullable', 'exists:savings_goals,id'],
            'items.*.name' => ['nullable', 'string', 'max:64'],
            'items.*.icon' => ['nullable', 'string', 'max:64'],
        ]);

        $user = $request->user();
        $errors = [];

        $items = collect($data['items'])->map(function ($row) use ($user, &$errors) {
            if ($row['kind'] === 'custom') {
                $cat = Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $row['name'], 'type' => $row['type']],
                    ['icon' => $row['icon'] ?? null, 'monthly_limit' => 0]
                );
                $row['category_id'] = $cat->id;
            }

            if (!empty($row['category_id'])) {
                $cat = Category::find($row['category_id']);
                if ($cat) {
                    if ($cat->type === 'saving' && $row['type'] !== 'saving') {
                        $errors[] = "Category '{$cat->name}' is a Saving type; item must be saving.";
                    }
                    if (in_array($cat->type, ['income', 'expense']) && $cat->type !== $row['type']) {
                        $errors[] = "Category '{$cat->name}' is {$cat->type}; item type must match.";
                    }
                    // 'both' accepts income or expense
                }
            }

            return [
                'kind' => $row['kind'],
                'type' => $row['type'],
                'amount' => (float) $row['amount'],
                'category_id' => $row['category_id'] ?? null,
                'savings_goal_id' => $row['savings_goal_id'] ?? null,
            ];
        });

        if (!empty($errors)) {
            return back()->withErrors(['items' => implode(' ', $errors)])->withInput();
        }

        $allocateSum = $items->whereIn('type', ['expense', 'saving'])->sum('amount');
        if ($allocateSum > (float) $step1['total_amount']) {
            return back()->withErrors(['items' => 'Allocated amount exceeds total budget.'])->withInput();
        }

        $request->session()->put('budget_wizard.items', $items->values()->all());

        return redirect()->route('budgets.create', ['step' => 3]);
    }

    /** GET Review page */
    protected function review(Request $request)
    {
        $step1 = $request->session()->get('budget_wizard.step1');
        $items = collect($request->session()->get('budget_wizard.items', []));

        if (!$step1 || $items->isEmpty()) {
            return redirect()
                ->route('budgets.create')
                ->with('status', 'Start by filling Step 1.');
        }

        $unallocated = (float) $step1['total_amount'] - (float) $items->whereIn('type', ['expense', 'saving'])->sum('amount');

        return view('app.budgets.review', compact('step1', 'items', 'unallocated'));
    }

    /** Final store + activate */
    public function store(Request $request)
    {
        $step1 = $request->session()->get('budget_wizard.step1');
        $items = collect($request->session()->get('budget_wizard.items', []));

        if (!$step1 || $items->isEmpty()) {
            return redirect()
                ->route('budgets.create')
                ->with('status', 'Your wizard session expired. Please create the budget again.');
        }

        $user = $request->user();

        $budget = DB::transaction(function () use ($user, $step1, $items) {
            /** @var Budget $budget */
            $budget = Budget::create([
                'user_id' => $user->id,
                'name' => $step1['name'],
                'period' => $step1['period'],
                'total_amount' => $step1['total_amount'],
                'start_date' => $step1['start_date'] ?? null,
                'end_date' => $step1['end_date'] ?? null,
                'is_active' => true,
                'activated_at' => now(),
            ]);

            foreach ($items as $row) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'category_id' => $row['category_id'] ?? null,
                    'savings_goal_id' => $row['savings_goal_id'] ?? null,
                    'type' => $row['type'],
                    'amount' => $row['amount'],
                ]);
            }

            return $budget;
        });

        // clear wizard
        $request->session()->forget('budget_wizard');

        return redirect()->route('dashboard')->with('status', 'Budget “' . $budget->name . '” activated.');
    }
        /**
     * View a single budget (category) with period spending and transactions.
     */
    public function show(Request $request, Category $budget)
    {
        $this->authorizeBudget($request, $budget);

        // Period selection: monthly | weekly | custom (YYYY-MM-DD .. YYYY-MM-DD)
        $period = $request->query('period', 'monthly');
        $now    = Carbon::now();

        if ($period === 'weekly') {
            $start = (clone $now)->startOfWeek(Carbon::MONDAY);
            $end   = (clone $now)->endOfWeek(Carbon::SUNDAY);
        } elseif ($period === 'custom') {
            $start = Carbon::parse($request->query('start', $now->startOfMonth()->toDateString()))->startOfDay();
            $end   = Carbon::parse($request->query('end',   $now->endOfMonth()->toDateString()))->endOfDay();
        } else {
            $period = 'monthly';
            $start  = (clone $now)->startOfMonth();
            $end    = (clone $now)->endOfMonth();
        }

        // Active non-monthly budgets overlapping the window (weekly/custom)
        $extraBudgets = CategoryBudget::query()
            ->where('user_id', $request->user()->id)
            ->where('category_id', $budget->id)
            ->where('is_active', true)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($qq) use ($start, $end) {
                    $qq->whereNull('end_date')
                       ->whereDate('start_date', '<=', $end->toDateString());
                })->orWhere(function ($qq) use ($start, $end) {
                    $qq->whereDate('start_date', '<=', $end->toDateString())
                       ->whereDate('end_date', '>=', $start->toDateString());
                });
            })
            ->orderByDesc('created_at')
            ->get();

        // Choose a limit for the current view: prefer matching period else fallback to monthly_limit
        $periodMatch = $extraBudgets->firstWhere('period', $period);
        $activeLimit = $periodMatch ? (float)$periodMatch->amount : (float)($budget->monthly_limit ?? 0);

        // Transactions for this category & window
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->where('type', 'expense')
            ->where('category_id', $budget->id)
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->latest('occurred_at')
            ->paginate(15);

        $spent = (float) Transaction::where('user_id', $request->user()->id)
            ->where('type', 'expense')
            ->where('category_id', $budget->id)
            ->whereBetween('occurred_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        return view('app.budgets.show', [
            'budget'       => $budget,
            'period'       => $period,
            'start'        => $start,
            'end'          => $end,
            'transactions' => $transactions,
            'spent'        => $spent,
            'activeLimit'  => $activeLimit,
            'extraBudgets' => $extraBudgets,
        ]);
    }

    private function authorizeBudget(Request $request, Category $budget): void
    {
        abort_unless($budget->user_id === $request->user()->id, 403);
    }

    /** Optional: destroy a budget */
    public function destroy(Request $request, Budget $budget)
    {
        abort_unless($budget->user_id === $request->user()->id, 403);
        $budget->delete();

        return redirect()->route('budgets.index')->with('status', 'Budget deleted.');
    }
}
