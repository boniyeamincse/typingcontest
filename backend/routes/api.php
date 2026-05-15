<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\ContestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ── Public auth ──────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-general');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth-general');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth-general');
        Route::post('/email/verify', [AuthController::class, 'verifyEmail'])->middleware('throttle:auth-general');
        Route::post('/social/google', [AuthController::class, 'socialGoogle'])->middleware('throttle:auth-general');
        Route::post('/social/github', [AuthController::class, 'socialGithub'])->middleware('throttle:auth-general');
    });

    // ── Public contest reads ──────────────────────────────────────────────────────
    Route::get('/contests', [ContestController::class, 'index']);
    Route::get('/contests/{contest}', [ContestController::class, 'show']);
    Route::get('/contests/{contest}/leaderboard', [ContestController::class, 'leaderboard']);
    Route::get('/leaderboard', [ContestController::class, 'globalLeaderboard']);
    Route::get('/leaderboard/daily', [ContestController::class, 'dailyLeaderboard']);
    Route::get('/leaderboard/weekly', [ContestController::class, 'weeklyLeaderboard']);
    Route::get('/leaderboard/monthly', [ContestController::class, 'monthlyLeaderboard']);
    Route::get('/leaderboard/country', [ContestController::class, 'countryLeaderboard']);

    // ── Authenticated ─────────────────────────────────────────────────────────────
    Route::middleware(['auth:api', 'verified.api'])->group(function () {
        // Auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Contest participation
        Route::post('/contests/{contest}/join', [ContestController::class, 'join']);
        Route::get('/contests/{contest}/typing-text', [ContestController::class, 'getTypingText']);
        Route::post('/contests/{contest}/submit', [ContestController::class, 'submit']);
        Route::get('/contests/{contest}/result', [ContestController::class, 'getUserResult']);

        // User profile/stats
        Route::get('/users/{username}', [ContestController::class, 'getUserProfile']);
        Route::get('/profile/history', [ContestController::class, 'getContestHistory']);
        Route::get('/profile/stats', [ContestController::class, 'getUserStats']);
        Route::get('/profile/badges', [ContestController::class, 'getUserBadges']);

        // Admin contest management
        Route::middleware('role:admin')->group(function () {
            Route::post('/admin/contests', [ContestController::class, 'store']);
            Route::put('/admin/contests/{contest}', [ContestController::class, 'update']);
            Route::delete('/admin/contests/{contest}', [ContestController::class, 'destroy']);
            Route::post('/admin/contests/{contest}/publish', [ContestController::class, 'publish']);
            Route::post('/admin/contests/{contest}/cancel', [ContestController::class, 'cancel']);
            Route::get('/admin/contests', [ContestController::class, 'adminList']);
        });
    });
});
