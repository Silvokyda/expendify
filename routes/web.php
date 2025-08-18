<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::view('/', 'welcome')->name('home');

/**
 * Public / Guest
 */
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login',  [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Register
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Forgot password (request link)
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Reset password (with token)
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

/**
 * Authenticated
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'app.dashboard')->name('dashboard');

    Route::view('/transactions', 'app.transactions.index')->name('transactions.index');
    Route::view('/categories',   'app.categories.index')->name('categories.index');
    Route::view('/savings',      'app.savings.index')->name('savings.index');
    Route::view('/reports',      'app.reports.index')->name('reports.index');
    Route::view('/reminders',    'app.reminders.index')->name('reminders.index');
    Route::view('/settings',     'app.settings')->name('settings');

    // Profile (controller-based)
    Route::get('/profile',               [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile',               [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',      [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',            [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout (use your LoginController::destroy)
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

// If you KEEP routes/auth.php for verify-email only:
require __DIR__.'/auth.php';
