<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ── Public auth ──────────────────────────────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ── Public contest reads ──────────────────────────────────────────────────────
    Route::get('/contests', [ContestController::class, 'index']);
    Route::get('/contests/{contest}', [ContestController::class, 'show']);
    Route::get('/contests/{contest}/leaderboard', [ContestController::class, 'leaderboard']);
    Route::get('/leaderboard', [ContestController::class, 'globalLeaderboard']);

    // ── Authenticated ─────────────────────────────────────────────────────────────
    Route::middleware('auth:api')->group(function () {
        // Auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Contest participation
        Route::post('/contests/{contest}/join', [ContestController::class, 'join']);
        Route::post('/contests/{contest}/submit', [ContestController::class, 'submit']);

        // Admin contest management
        Route::post('/admin/contests', [ContestController::class, 'store']);
        Route::patch('/admin/contests/{contest}', [ContestController::class, 'update']);
        Route::post('/admin/contests/{contest}/publish', [ContestController::class, 'publish']);
    });
});
