<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\SavingsGoalController;
use App\Http\Controllers\Api\V1\SavingsContributionController;

Route::prefix('v1')->group(function () {
    // Public
    Route::get('ping', fn() => response()->json(['status' => 'ok']));
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);

    // Protected (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [MeController::class, 'show']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('savings-goals', SavingsGoalController::class);
        Route::apiResource('savings-contributions', SavingsContributionController::class);

        // Reports
        Route::get('reports/summary/monthly', [TransactionController::class, 'monthlySummary']);
        Route::get('reports/summary/yearly',  [TransactionController::class, 'yearlySummary']);
    });
});
