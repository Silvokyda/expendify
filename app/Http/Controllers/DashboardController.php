<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Category;
use App\Models\Budget;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ---------------- Period window (default monthly)
        $period = $request->query('period', 'monthly');
        if ($period === 'weekly') {
            $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $end = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        } elseif ($period === 'custom') {
            $start = Carbon::parse($request->query('start', Carbon::now()->startOfMonth()->toDateString()))->startOfDay();
            $end = Carbon::parse($request->query('end', Carbon::now()->endOfMonth()->toDateString()))->endOfDay();
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $period = 'monthly';
        }

        // ---------------- Categories & (legacy) limits
        $categoriesQuery = method_exists(Category::query()->getModel(), 'scopeForUser')
            ? Category::forUser($user->id)->whereIn('type', ['expense', 'both'])
            : $user->categories()->whereIn('type', ['expense', 'both']);

        $categories = $categoriesQuery
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'monthly_limit']);

        $limitsByCategory = collect();
        if (method_exists($user, 'categoryBudgets')) {
            $budgets = $user->categoryBudgets()
                ->where('period', $period)
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->get(['category_id', 'amount']);
            $limitsByCategory = $budgets->unique('category_id')->pluck('amount', 'category_id');
        }
        if ($period === 'monthly') {
            foreach ($categories as $c) {
                if (!isset($limitsByCategory[$c->id]) && (float) ($c->monthly_limit ?? 0) > 0) {
                    $limitsByCategory[$c->id] = (float) $c->monthly_limit;
                }
            }
        }

        // ---------------- Spend, income, aggregates for window
        $spentThisPeriod = (float) $user->transactions()->expense()->betweenDates($start, $end)->sum('amount');
        $incomeThisPeriod = (float) $user->transactions()->income()->betweenDates($start, $end)->sum('amount');

        $spendByCategory = $user->transactions()
            ->expense()->betweenDates($start, $end)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->toArray();

        $totalBudget = (float) collect($limitsByCategory)->sum();
        $remaining = max($totalBudget - $spentThisPeriod, 0);

        // ---------------- Recent transactions for list
        $recentTx = $user->transactions()
            ->with('category')
            ->latest('occurred_at')
            ->limit(6)
            ->get(['id', 'category_id', 'type', 'amount', 'occurred_at as date', 'note as merchant']);

        // ---------------- Savings & wallet (vars Blade already uses)
        $goalTarget = 0.0;
        $goalSaved = 0.0;
        if (method_exists($user, 'savingsGoals')) {
            $g = $user->savingsGoals()->withSum('contributions as contributed', 'amount')
                ->first(['id', 'name', 'target_amount']);
            $goalTarget = (float) ($g->target_amount ?? 0);
            $goalSaved = (float) ($g->contributed ?? 0);
        }
        $wallet = method_exists($user, 'wallet') ? $user->wallet()->first() : null;
        $hasWallet = (bool) $wallet;
        $walletBalance = $wallet ? (float) $wallet->balance : 0.0;

        $isNewUser = ($categories->count() === 0) && ($recentTx->count() === 0) && ($goalTarget <= 0);

        // ---------------- Chart datasets (pie, balances line, period compare, this month bar)
        $days = (int) $start->diffInDays($end) + 1;
        $labels = [];
        $dailyNet = [];        // current period daily net (income - expense)
        $cumBalance = [];      // cumulative net across current period

        // pull all tx for current window
        $tx = $user->transactions()
            ->betweenDates($start, $end)
            ->get(['occurred_at', 'type', 'amount'])
            ->groupBy(fn($t) => Carbon::parse($t->occurred_at)->toDateString());

        $running = 0;
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i);
            $key = $d->toDateString();
            $labels[] = $d->format('M j');

            $income = ($tx[$key] ?? collect())->where('type', 'income')->sum('amount');
            $expense = ($tx[$key] ?? collect())->where('type', 'expense')->sum('amount');
            $net = (float) $income - (float) $expense;

            $dailyNet[] = $net;
            $running += $net;
            $cumBalance[] = $running;
        }

        // previous period window (for comparison)
        if ($period === 'weekly') {
            $prevStart = $start->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
            $prevEnd = $prevStart->copy()->endOfWeek(Carbon::SUNDAY);
        } elseif ($period === 'monthly') {
            $prevStart = $start->copy()->subMonth()->startOfMonth();
            $prevEnd = $prevStart->copy()->endOfMonth();
        } else {
            $span = $days;
            $prevStart = $start->copy()->subDays($span);
            $prevEnd = $start->copy()->subDay();
        }

        $txPrev = $user->transactions()
            ->betweenDates($prevStart, $prevEnd)
            ->get(['occurred_at', 'type', 'amount'])
            ->groupBy(fn($t) => Carbon::parse($t->occurred_at)->toDateString());

        $prevDailyNet = [];
        $prevCum = [];
        $runningPrev = 0;
        for ($i = 0; $i < $days; $i++) {
            $d = $prevStart->copy()->addDays($i);
            if ($d->gt($prevEnd)) {
                $prevDailyNet[] = 0;
                $prevCum[] = $runningPrev;
                continue;
            }
            $key = $d->toDateString();
            $inc = ($txPrev[$key] ?? collect())->where('type', 'income')->sum('amount');
            $exp = ($txPrev[$key] ?? collect())->where('type', 'expense')->sum('amount');
            $net = (float) $inc - (float) $exp;
            $prevDailyNet[] = $net;
            $runningPrev += $net;
            $prevCum[] = $runningPrev;
        }

        // pie: if we have a budget total, show Spent vs Remaining; else In vs Out
        if ($totalBudget > 0) {
            $pieLabels = ['Spent', 'Remaining'];
            $pieValues = [$spentThisPeriod, $remaining];
        } else {
            $pieLabels = ['Expenses', 'Income'];
            $pieValues = [$spentThisPeriod, $incomeThisPeriod];
        }

        $chartData = [
            'pie' => [
                'labels' => $pieLabels,
                'data' => array_map('floatval', $pieValues),
            ],
            'balances' => [
                'labels' => $labels,
                'current' => array_map('floatval', $cumBalance),
                'previous' => array_map('floatval', $prevCum),
            ],
            'periodCompare' => [
                'labels' => $labels,
                'current' => array_map('floatval', $dailyNet),
                'previous' => array_map('floatval', $prevDailyNet),
            ],
            'thisMonth' => [
                'income' => (float) $incomeThisPeriod,
                'expense' => (float) $spentThisPeriod,
            ],
        ];

        // ---------------- Render (keeps every legacy var your Blade uses)
        return view('app.dashboard.index', [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'isNewUser' => $isNewUser,
            'hasWallet' => $hasWallet,
            'walletBalance' => $walletBalance,
            'spentThisMonth' => $spentThisPeriod,
            'totalBudget' => $totalBudget,
            'remaining' => $remaining,
            'recentTx' => $recentTx,
            'categories' => $categories,
            'spendByCategory' => $spendByCategory,
            'limitsByCategory' => $limitsByCategory,
            'goalTarget' => $goalTarget,
            'goalSaved' => $goalSaved,
            'upcoming' => collect(),
            'budgetSummary' => null,

            // NEW
            'chartData' => $chartData,
        ]);
    }

    // -------- Legacy onboarding → redirect into new budget flow
    public function showOnboarding(Request $request)
    {
        return redirect()->route('budgets.create');
    }
    public function saveBudget(Request $request)
    {
        return redirect()->route('budgets.create');
    }
    public function saveWalletChoice(Request $request)
    {
        return redirect()->route('dashboard');
    }
}
