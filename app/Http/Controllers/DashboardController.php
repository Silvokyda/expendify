<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryBudget;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $period = $request->query('period', 'monthly');

        if ($period === 'weekly') {
            $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $end   = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        } elseif ($period === 'custom') {
            $start = Carbon::parse($request->query('start', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
            $end   = Carbon::parse($request->query('end', Carbon::now()->endOfMonth()->toDateString()))->endOfDay();
        } else {
            $start = Carbon::now()->startOfMonth();
            $end   = Carbon::now()->endOfMonth();
            $period = 'monthly';
        }

        $categories = $user->categories()
            ->expense()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'monthly_limit']);

        $budgets = $user->categoryBudgets()
            ->where('period', $period)
            ->where('is_active', true)
            ->overlapping($start->toDateString(), $end->toDateString())
            ->orderByDesc('created_at')
            ->get(['category_id', 'amount', 'period', 'start_date', 'end_date', 'created_at']);

        // pick the newest budget per category
        $limitsByCategory = $budgets->unique('category_id')->pluck('amount', 'category_id');

        // Fallback to categories.monthly_limit ONLY for monthly period
        if ($period === 'monthly') {
            foreach ($categories as $c) {
                if (!isset($limitsByCategory[$c->id]) && (float) ($c->monthly_limit ?? 0) > 0) {
                    $limitsByCategory[$c->id] = (float) $c->monthly_limit;
                }
            }
        }

        // -------- Spend this period (expenses only)
        $spentThisPeriod = (float) $user->transactions()
            ->expense()
            ->betweenDates($start, $end)
            ->sum('amount');

        // Per‑category spend
        $spendByCategory = $user->transactions()
            ->expense()
            ->betweenDates($start, $end)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // -------- Totals
        $totalBudget = (float) collect($limitsByCategory)->sum();
        $remaining   = max($totalBudget - $spentThisPeriod, 0);

        // -------- Recent transactions (same aliasing you used)
        $recentTx = $user->transactions()
            ->latest('occurred_at')->limit(6)
            ->get(['id', 'category_id', 'type', 'amount', 'occurred_at as date', 'note as merchant']);

        // Savings goals and wallet (unchanged)
        $goalTarget = 0;
        $goalSaved = 0;
        if (method_exists($user, 'savingsGoals')) {
            $g = $user->savingsGoals()->withSum('contributions as contributed', 'amount')
                ->first(['id', 'name', 'target_amount']);
            $goalTarget = (float) ($g->target_amount ?? 0);
            $goalSaved  = (float) ($g->contributed ?? 0);
        }

        $hasWallet     = (bool) ($user->has_wallet ?? false);
        $walletBalance = $hasWallet ? (float) optional($user->wallet)->balance : 0.0;

        $isNewUser = ($categories->count() === 0) && ($recentTx->count() === 0) && ($goalTarget <= 0);

        return view('app.dashboard.index', [
            'period'          => $period,
            'start'           => $start,
            'end'             => $end,
            'isNewUser'       => $isNewUser,
            'hasWallet'       => $hasWallet,
            'walletBalance'   => $walletBalance,
            'spentThisMonth'  => $spentThisPeriod,
            'totalBudget'     => $totalBudget,
            'remaining'       => $remaining,
            'recentTx'        => $recentTx,
            'categories'      => $categories,
            'spendByCategory' => $spendByCategory,
            'goalTarget'      => $goalTarget,
            'goalSaved'       => $goalSaved,
            'limitsByCategory' => $limitsByCategory,
            'upcoming'        => collect(),
        ]);
    }

    public function showOnboarding(Request $request)
    {
        $user = $request->user();

        // If wallet already set up, go to dashboard
        if ($user->has_wallet ?? false) {
            return redirect()->route('dashboard');
        }

        // Parse step (1 or 2)
        $step = (int) $request->query('step', 1);
        if (!in_array($step, [1, 2], true)) {
            $step = 1;
        }

        // Check existence of categories independently of what we load below
        $hasCategories = $user->categories()->expense()->exists();

        // Block step 2 if the user has no categories yet
        if ($step === 2 && !$hasCategories) {
            return redirect()
                ->route('onboarding.show', ['step' => 1])
                ->with('warning', 'Please create at least one budget category before proceeding to the next step.');
        }

        // Data containers
        $categories  = collect();
        $budgetItems = collect();

        if ($step === 1) {
            // Load the categories for listing on step 1
            $categories = $user->categories()
                ->expense()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'monthly_limit', 'created_at']);

            // Merge in any CategoryBudget rows (optional)
            $budgetsByCategory = collect();
            if (class_exists(\App\Models\CategoryBudget::class) && $categories->isNotEmpty()) {
                $allBudgets = CategoryBudget::query()
                    ->where('user_id', $user->id)
                    ->whereIn('category_id', $categories->pluck('id'))
                    ->orderByDesc('created_at')
                    ->get(['id', 'category_id', 'period', 'amount', 'start_date', 'end_date', 'is_active', 'created_at']);

                $budgetsByCategory = $allBudgets->groupBy('category_id');
            }

            $budgetItems = $categories->map(function ($cat) use ($budgetsByCategory) {
                $chosen = optional($budgetsByCategory->get($cat->id))->firstWhere('is_active', true)
                    ?? optional($budgetsByCategory->get($cat->id))->first();

                return (object) [
                    'category_id'   => $cat->id,
                    'name'          => $cat->name,
                    'created_at'    => $cat->created_at,
                    'monthly_limit' => (float) ($cat->monthly_limit ?? 0),
                    'period'        => $chosen->period    ?? 'monthly',
                    'amount'        => (float) ($chosen->amount ?? $cat->monthly_limit ?? 0),
                    'start_date'    => $chosen->start_date ?? null,
                    'end_date'      => $chosen->end_date   ?? null,
                    'is_active'     => $chosen->is_active  ?? true,
                    '_category'     => $cat,
                    '_budget'       => $chosen,
                ];
            });
        }

        return view('app.onboarding.index', [
            'step'        => $step,
            'categories'  => $categories,
            'budgetItems' => $budgetItems,
        ]);
    }

    public function saveBudget(Request $request)
    {
        // If new modal form present (has 'name'), handle that path
        if ($request->filled('name')) {
            $data = $request->validate([
                'name'        => ['required', 'string', 'max:64'],
                'amount'      => ['nullable', 'numeric', 'min:0'],
                'period'      => ['nullable', 'in:monthly,weekly,custom'],
                'start_date'  => ['nullable', 'date'],
                'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            ]);

            $user = $request->user();

            /** @var \App\Models\Category $category */
            $category = Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                ['type' => 'expense', 'monthly_limit' => 0]
            );

            $amount = (float) ($data['amount'] ?? 0);

            // If you created the category_budgets table, write non-monthly there.
            if ($amount > 0) {
                $period = $data['period'] ?? 'monthly';

                if (class_exists(\App\Models\CategoryBudget::class) && in_array($period, ['weekly', 'custom'], true)) {
                    // Save to per-period budgets
                    CategoryBudget::create([
                        'user_id'     => $user->id,
                        'category_id' => $category->id,
                        'period'      => $period,
                        'amount'      => $amount,
                        'start_date'  => $period === 'custom' ? $data['start_date'] : null,
                        'end_date'    => $period === 'custom' ? $data['end_date'] : null,
                        'is_active'   => true,
                    ]);
                } else {
                    // Monthly by default → store on categories.monthly_limit
                    $category->forceFill(['monthly_limit' => $amount])->save();
                }
            }

            // Stay on step 1 (so they can add more), but you can also go to step 2.
            return redirect()->route('onboarding.show', ['step' => 1])
                ->with('status', 'Budget saved.');
        }

        // Legacy bulk add (array): keep it for keyboard users / past form
        $data = $request->validate([
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['nullable', 'string', 'max:64'],
        ]);

        $names = collect($data['categories'] ?? [])
            ->map(fn($s) => trim((string) $s))
            ->filter()
            ->unique()
            ->take(30);

        foreach ($names as $name) {
            Category::firstOrCreate([
                'user_id' => $request->user()->id,
                'name'    => $name,
                'type'    => 'expense',
            ]);
        }

        return redirect()->route('onboarding.show', ['step' => 2]);
    }

    public function saveWalletChoice(Request $request)
    {
        $data = $request->validate([
            'choice' => ['required', 'in:wallet,skip'],
            'msisdn' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();

        if ($data['choice'] === 'wallet') {
            $request->validate(['msisdn' => ['required', 'string', 'max:20']]);

            DB::transaction(function () use ($user, $data) {
                Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['msisdn'  => $data['msisdn'], 'balance' => 0]
                );
                $user->forceFill(['has_wallet' => true])->save();
            });
        } else {
            // tracking-only mode
            $user->forceFill(['has_wallet' => false])->save();
        }

        return redirect()->route('dashboard');
    }
}
