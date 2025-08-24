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

    /** Step 1 (GET) + step router */
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
            return $this->review($request);
        }

        // Step 1 view
        return view('app.budgets.create-step1');
    }

    /** POST Step 1 */
    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'period' => ['required', 'in:monthly,weekly,custom'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $request->session()->put('budget_wizard.step1', $data);

        return redirect()->route('budgets.create', ['step' => 2]);
    }

    /** GET Step 2 (internal) */
    protected function step2(Request $request)
    {
        $categories = Category::query()
            ->where(fn($q) => $q->where('user_id', $request->user()->id)->orWhereNull('user_id'))
            ->orderBy('name')
            ->get();

        $savingsGoals = method_exists($request->user(), 'savingsGoals')
            ? $request->user()->savingsGoals()->orderBy('name')->get()
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

    /** NEW: budgets.show */
    public function show(Request $request, Budget $budget)
    {
        abort_if($budget->user_id !== $request->user()->id, 403);

        $budget = Budget::query()
            ->whereKey($budget->id)
            ->with(['items.category', 'items.savingsGoal'])
            ->firstOrFail();

        return view('app.budgets.show', compact('budget'));
    }

    /** NEW: budgets.edit */
    public function edit(Request $request, Budget $budget)
    {
        abort_if($budget->user_id !== $request->user()->id, 403);

        $budget->load(['items.category']);

        $categories = Category::query()
            ->where(fn($q) => $q->where('user_id', $request->user()->id)->orWhereNull('user_id'))
            ->whereIn('type', ['expense', 'both']) // expense-only allocations by design
            ->orderBy('name')
            ->get();

        return view('app.budgets.edit', compact('budget', 'categories'));
    }

    /** NEW: budgets.update */
    public function update(Request $request, Budget $budget)
    {
        abort_if($budget->user_id !== $request->user()->id, 403);

        $base = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'period' => ['required', 'in:monthly,weekly,custom'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $allocs = $request->input('allocs', []);
        $allocsNew = $request->input('allocs_new', []);
        $newCategories = $request->input('new_categories', []);

        DB::transaction(function () use ($budget, $base, $allocs, $allocsNew, $newCategories, $request) {
            $budget->update([
                'name' => $base['name'],
                'period' => $base['period'],
                'total_amount' => $base['total_amount'],
                'start_date' => $base['start_date'] ?? null,
                'end_date' => $base['end_date'] ?? null,
                'is_active' => (bool) ($base['is_active'] ?? $budget->is_active),
            ]);

            // Soft delete flags for existing items
            $idsToDelete = [];
            foreach ($allocs as $a) {
                if (!empty($a['id']) && !empty($a['_delete'])) {
                    $idsToDelete[] = (int) $a['id'];
                }
            }
            if ($idsToDelete) {
                BudgetItem::query()
                    ->where('budget_id', $budget->id)
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }

            // Update existing items (expense-only)
            foreach ($allocs as $a) {
                if (isset($a['_delete']))
                    continue;
                if (!empty($a['id'])) {
                    BudgetItem::query()
                        ->where('budget_id', $budget->id)
                        ->where('id', $a['id'])
                        ->update([
                            'type' => 'expense',
                            'category_id' => $a['category_id'] ?? null,
                            'amount' => (float) ($a['amount'] ?? 0),
                        ]);
                }
            }

            // Insert new allocations tied to existing categories
            foreach ($allocsNew as $a) {
                if (empty($a['category_id']))
                    continue;
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'type' => 'expense',
                    'category_id' => $a['category_id'],
                    'amount' => (float) ($a['amount'] ?? 0),
                ]);
            }

            // Insert brand-new categories (expense-only) + allocation
            foreach ($newCategories as $c) {
                if (empty($c['name']))
                    continue;

                $cat = Category::firstOrCreate(
                    ['user_id' => $request->user()->id, 'name' => $c['name'], 'type' => 'expense'],
                    ['icon' => null, 'monthly_limit' => 0]
                );

                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'type' => 'expense',
                    'category_id' => $cat->id,
                    'amount' => (float) ($c['amount'] ?? 0),
                ]);
            }
        });

        $budget->touch(); // ensure updated_at reflects item changes
        return redirect()->route('budgets.show', ['budget' => $budget->id, '_' => now()->timestamp]);
    }

    /** Optional: delete budget */
    public function destroy(Request $request, Budget $budget)
    {
        abort_if($budget->user_id !== $request->user()->id, 403);
        $budget->delete();

        return redirect()->route('budgets.index')->with('status', 'Budget deleted.');
    }
}
