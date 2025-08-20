<?php

use Illuminate\Support\Facades\Route;
// Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BudgetController;

// Middleware
use App\Http\Middleware\EnsureHasWallet;

//
// Public (Blade) pages
//
Route::view('/', 'welcome')->name('home');

//
// Guest-only auth pages (Blade)
//
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login',  [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Register
    Route::get('/register',  [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Forgot password (request link)
    Route::get('/forgot-password',  [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // Reset password (with token)
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',       [NewPasswordController::class, 'store'])->name('password.update');
});

//
// Authenticated (Blade) pages
//
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Onboarding wizard now lives in DashboardController ---
    Route::get('/onboarding', [DashboardController::class, 'showOnboarding'])->name('onboarding.show');
    Route::post('/onboarding/budget', [DashboardController::class, 'saveBudget'])->name('onboarding.budget');
    Route::post('/onboarding/wallet', [DashboardController::class, 'saveWalletChoice'])->name('onboarding.wallet');

    // --- Wallet (API-ish action from a form)
    Route::post('/wallet', [WalletController::class, 'store'])->name('wallet.store');

    // --- Payouts (used by the “Pay with M‑PESA” button)
    Route::middleware(EnsureHasWallet::class)->group(function () {
        Route::view('/payouts/create', 'app.payouts.create')->name('payouts.create');
        Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
    });

    // --- Transactions (used by “+ Add Expense”)
    Route::view('/transactions',  [TransactionController::class, 'index'])->name('transactions.index');
    Route::view('/transactions/create',  [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    Route::resource('budgets', BudgetController::class)
    ->only(['index','create','store','edit','update','destroy']);

    // --- Static sections
    Route::view('/categories', 'app.categories.index')->name('categories.index');
    Route::view('/savings',    'app.savings.index')->name('savings.index');
    Route::view('/reports',    'app.reports.index')->name('reports.index');
    Route::view('/reminders',  'app.reminders.index')->name('reminders.index');
    Route::view('/settings',   'app.settings')->name('settings');

    // --- Profile
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Logout (Breeze)
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Keep this if you use Laravel's email verification routes in auth.php
require __DIR__ . '/auth.php';
