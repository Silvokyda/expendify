<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;

use App\Http\Middleware\EnsureHasWallet;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/onboarding', [DashboardController::class, 'showOnboarding'])->name('onboarding');
    Route::post('/onboarding/budget', [DashboardController::class, 'saveBudget'])->name('onboarding.budget');
    Route::post('/onboarding/wallet', [DashboardController::class, 'saveWalletChoice'])->name('onboarding.wallet');

    Route::post('/wallet', [WalletController::class, 'store'])->name('wallet.store');

    Route::middleware(EnsureHasWallet::class)->group(function () {
        Route::view('/payouts/create', 'app.payouts.create')->name('payouts.create');
        Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
    });

    Route::resource('budgets', BudgetController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::post('/budgets/wizard/step1', [BudgetController::class, 'storeStep1'])->name('budgets.wizard.step1');
    Route::post('/budgets/wizard/step2', [BudgetController::class, 'storeStep2'])->name('budgets.wizard.step2');
    Route::get('/budgets/review', [BudgetController::class, 'review'])->name('budgets.review');

    Route::get('/budgets/{budget}', function (Budget $budget) {
        abort_if($budget->user_id !== auth()->id(), 403);
        $budget = Budget::query()
            ->whereKey($budget->id)
            ->with(['items.category', 'items.savingsGoal'])
            ->firstOrFail();
        return view('app.budgets.show', compact('budget'));
    })->name('budgets.show');

    Route::get('/budgets/{budget}/edit', function (Budget $budget) {
        abort_if($budget->user_id !== auth()->id(), 403);
        $budget->load(['items.category']);
        $categories = Category::query()
            ->where(fn($q) => $q->where('user_id', auth()->id())->orWhereNull('user_id'))
            ->whereIn('type', ['expense', 'both'])
            ->orderBy('name')
            ->get();
        return view('app.budgets.edit', compact('budget', 'categories'));
    })->name('budgets.edit');

    Route::put('/budgets/{budget}', function (Request $request, Budget $budget) {
        abort_if($budget->user_id !== auth()->id(), 403);

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

        $sumExisting = collect($allocs)->reject(fn($a) => isset($a['_delete']))->sum(fn($a) => (float) ($a['amount'] ?? 0));
        $sumNew = collect($allocsNew)->sum(fn($a) => (float) ($a['amount'] ?? 0));
        $sumNewCats = collect($newCategories)->sum(fn($a) => (float) ($a['amount'] ?? 0));
        $allocatedTotal = $sumExisting + $sumNew + $sumNewCats;
        if ($allocatedTotal > (float) $base['total_amount']) {
            return back()->withErrors(['allocations' => 'Allocated amount exceeds budget total.'])->withInput();
        }

        if ($base['period'] !== 'custom') {
            $base['start_date'] = null;
            $base['end_date'] = null;
        }
        $base['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($budget, $base, $allocs, $allocsNew, $newCategories) {
            $budget->update($base);

            $deleteIds = collect($allocs)->filter(fn($a) => isset($a['_delete']) && !empty($a['id']))->pluck('id')->all();
            if (!empty($deleteIds)) {
                BudgetItem::query()->where('budget_id', $budget->id)->whereIn('id', $deleteIds)->delete();
            }

            foreach ($allocs as $a) {
                if (isset($a['_delete']))
                    continue;
                if (!empty($a['id'])) {
                    BudgetItem::query()->where('budget_id', $budget->id)->where('id', $a['id'])->update([
                        'type' => 'expense',
                        'category_id' => $a['category_id'] ?? null,
                        'amount' => (float) ($a['amount'] ?? 0),
                    ]);
                }
            }

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

            foreach ($newCategories as $c) {
                if (empty($c['name']))
                    continue;
                $cat = Category::firstOrCreate(
                    ['user_id' => auth()->id(), 'name' => $c['name'], 'type' => 'expense'],
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

        $budget->touch(); // make sure updated_at reflects item changes too
        return redirect()->route('budgets.show', ['budget' => $budget->id, '_' => now()->timestamp]);
    })->name('budgets.update');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    Route::view('/categories', 'app.categories.index')->name('categories.index');
    Route::view('/savings', 'app.savings.index')->name('savings.index');
    Route::view('/reports', 'app.reports.index')->name('reports.index');
    Route::view('/reminders', 'app.reminders.index')->name('reminders.index');
    Route::view('/settings', 'app.settings')->name('settings');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
