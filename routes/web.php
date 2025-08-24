<?php

use Illuminate\Support\Facades\Route;

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

    // Onboarding
    Route::get('/onboarding', [DashboardController::class, 'showOnboarding'])->name('onboarding');
    Route::post('/onboarding/budget', [DashboardController::class, 'saveBudget'])->name('onboarding.budget');
    Route::post('/onboarding/wallet', [DashboardController::class, 'saveWalletChoice'])->name('onboarding.wallet');

    // Wallet & payouts (requires an existing wallet)
    Route::post('/wallet', [WalletController::class, 'store'])->name('wallet.store');
    Route::middleware(EnsureHasWallet::class)->group(function () {
        Route::view('/payouts/create', 'app.payouts.create')->name('payouts.create');
        Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
    });

    // Budgets CRUD + wizard
    Route::resource('budgets', BudgetController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::post('/budgets/wizard/step1', [BudgetController::class, 'storeStep1'])->name('budgets.wizard.step1');
    Route::post('/budgets/wizard/step2', [BudgetController::class, 'storeStep2'])->name('budgets.wizard.step2');
    Route::get('/budgets/review', [BudgetController::class, 'review'])->name('budgets.review');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    // Static app sections
    Route::view('/categories', 'app.categories.index')->name('categories.index');
    Route::view('/savings', 'app.savings.index')->name('savings.index');
    Route::view('/reports', 'app.reports.index')->name('reports.index');
    Route::view('/reminders', 'app.reminders.index')->name('reminders.index');
    Route::view('/settings', 'app.settings')->name('settings');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
