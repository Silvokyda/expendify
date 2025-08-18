<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

    Route::middleware(['auth'])->group(function () {
        Route::view('dashboard', 'app.dashboard')->name('dashboard');
        Route::view('/transactions', 'app.transactions.index')->name('transactions.index');
        Route::view('/categories',   'app.categories.index')->name('categories.index');
        Route::view('/savings',      'app.savings.index')->name('savings.index');
        Route::view('/reports',      'app.reports.index')->name('reports.index');
        Route::view('/reminders',    'app.reminders.index')->name('reminders.index');
        Route::view('/settings',     'app.settings')->name('settings');
        Route::view('profile', 'app.profile')->name('profile');
        // logout
        Route::post('/logout', function () {
            Auth::logout();
            return redirect('/');
        })->name('logout');
    
        // profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

require __DIR__.'/auth.php';
