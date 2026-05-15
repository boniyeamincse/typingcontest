<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Contest\ContestController;
use App\Http\Controllers\API\Leaderboard\LeaderboardController;
use App\Http\Controllers\API\Payment\AdminPaymentController;
use App\Http\Controllers\API\Payment\PaymentController;
use App\Http\Controllers\API\Profile\ProfileController;
use App\Http\Controllers\API\Subscription\AdminSubscriptionController;
use App\Http\Controllers\API\Subscription\FeatureAccessController;
use App\Http\Controllers\API\Subscription\SubscriptionController;
use App\Http\Controllers\API\Typing\TypingController;
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

    // ── Public profile ───────────────────────────────────────────────────────────
    // NOTE: This wildcard route must be declared AFTER all specific /profile/* routes
    // to prevent it from matching sub-paths like /profile/stats or /profile/badges.
    // It is placed at the bottom of the v1 group for this reason.

    // ── Public contest reads ──────────────────────────────────────────────────────
    Route::get('/contests', [ContestController::class, 'index']);
    Route::get('/contests/{contest}', [ContestController::class, 'show']);
    Route::get('/contests/{contest}/leaderboard', [LeaderboardController::class, 'contest']);
    Route::get('/leaderboard', [LeaderboardController::class, 'global']);
    Route::get('/leaderboard/daily', [LeaderboardController::class, 'daily']);
    Route::get('/leaderboard/weekly', [LeaderboardController::class, 'weekly']);
    Route::get('/leaderboard/monthly', [LeaderboardController::class, 'monthly']);
    Route::get('/leaderboard/country/{countryCode}', [LeaderboardController::class, 'country']);
    Route::get('/leaderboard/global', [LeaderboardController::class, 'global']);
    Route::get('/leaderboard/contest/{contest}', [LeaderboardController::class, 'contest']);

    // Subscription plans
    Route::get('/plans', [SubscriptionController::class, 'plans']);

    // Payment webhook (public, signed)
    Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->middleware('throttle:payment-webhook');

    // ── Authenticated ─────────────────────────────────────────────────────────────
    Route::middleware(['auth:api', 'verified.api'])->group(function () {
        // Auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // ── Profile ─────────────────────────────────────────────────────────────
        Route::prefix('profile')->group(function () {
            Route::get('/',               [ProfileController::class, 'show']);
            Route::put('/update',         [ProfileController::class, 'update'])->middleware('throttle:30,1');
            Route::post('/avatar',        [ProfileController::class, 'uploadAvatar'])->middleware('throttle:10,1');
            Route::post('/cover',         [ProfileController::class, 'uploadCover'])->middleware('throttle:10,1');
            Route::get('/stats',          [ProfileController::class, 'stats']);
            Route::get('/badges',         [ProfileController::class, 'badges']);
            Route::post('/badges/feature',[ProfileController::class, 'featureBadge']);
            Route::get('/matches',        [ProfileController::class, 'matches']);
            Route::get('/activity',       [ProfileController::class, 'activity']);
        });

        // Contest participation
        Route::post('/contests/{contest}/join', [ContestController::class, 'join']);
        Route::get('/contests/{contest}/typing-text', [ContestController::class, 'getTypingText']);
        Route::post('/contests/{contest}/submit', [ContestController::class, 'submit']);
        Route::get('/contests/{contest}/result', [ContestController::class, 'getUserResult']);

        // Typing engine
        Route::prefix('typing')->group(function () {
            Route::post('/start', [TypingController::class, 'start'])->middleware('throttle:30,1');
            Route::post('/update', [TypingController::class, 'update'])->middleware('throttle:180,1');
            Route::post('/submit', [TypingController::class, 'submit'])->middleware('throttle:30,1');
            Route::get('/session/{id}', [TypingController::class, 'show']);
            Route::get('/status/{session_id}', [TypingController::class, 'status']);
            Route::get('/result/{id}', [TypingController::class, 'result']);
            Route::get('/history', [TypingController::class, 'history']);
        });

        // Subscription module
        Route::prefix('subscription')->group(function () {
            Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->middleware('throttle:10,1');
            Route::post('/upgrade', [SubscriptionController::class, 'upgrade'])->middleware('throttle:10,1');
            Route::get('/current', [SubscriptionController::class, 'current']);
            Route::post('/cancel', [SubscriptionController::class, 'cancel'])->middleware('throttle:10,1');
        });

        // Feature access control examples
        Route::prefix('features')->group(function () {
            Route::get('/contests', [FeatureAccessController::class, 'contests'])->middleware('subscription:contest');
            Route::get('/analytics', [FeatureAccessController::class, 'analytics'])->middleware('subscription:analytics');
            Route::get('/multiplayer', [FeatureAccessController::class, 'multiplayer'])->middleware('subscription:multiplayer');
            Route::get('/ai-coach', [FeatureAccessController::class, 'aiCoach'])->middleware('subscription:ai');
            Route::get('/premium-leaderboard', [FeatureAccessController::class, 'premiumLeaderboard'])->middleware('subscription:premium_leaderboard');
        });

        // Payment module
        Route::prefix('payment')->group(function () {
            Route::post('/create', [PaymentController::class, 'create'])->middleware('throttle:20,1');
            Route::post('/verify', [PaymentController::class, 'verify'])->middleware('throttle:20,1');
            Route::get('/history', [PaymentController::class, 'history']);
            Route::get('/invoice/{paymentIntentId}/download', [PaymentController::class, 'downloadInvoice']);
        });

        // Admin contest management
        Route::middleware('role:admin')->group(function () {
            Route::post('/admin/contests', [ContestController::class, 'store']);
            Route::put('/admin/contests/{contest}', [ContestController::class, 'update']);
            Route::delete('/admin/contests/{contest}', [ContestController::class, 'destroy']);
            Route::post('/admin/contests/{contest}/publish', [ContestController::class, 'publish']);
            Route::post('/admin/contests/{contest}/start', [ContestController::class, 'start']);
            Route::post('/admin/contests/{contest}/end', [ContestController::class, 'end']);
            Route::post('/admin/contests/{contest}/pause', [ContestController::class, 'pause']);
            Route::post('/admin/contests/{contest}/resume', [ContestController::class, 'resume']);
            Route::post('/admin/contests/{contest}/cancel', [ContestController::class, 'cancel']);

            // Admin coupon management
            Route::get('/admin/coupons', [AdminSubscriptionController::class, 'coupons']);
            Route::post('/admin/coupons', [AdminSubscriptionController::class, 'createCoupon'])->middleware('throttle:30,1');
            Route::put('/admin/coupons/{coupon}', [AdminSubscriptionController::class, 'updateCoupon'])->middleware('throttle:30,1');
            Route::post('/admin/coupons/{coupon}/toggle', [AdminSubscriptionController::class, 'toggleCoupon'])->middleware('throttle:30,1');

            // Admin subscription override
            Route::post('/admin/subscriptions/override', [AdminSubscriptionController::class, 'override'])->middleware('throttle:20,1');

            // Admin refund handling
            Route::get('/admin/reports/subscriptions-payments', [AdminPaymentController::class, 'report']);
            Route::get('/admin/reports/subscriptions-payments/export', [AdminPaymentController::class, 'export']);
            Route::post('/admin/payments/{paymentIntentId}/refund', [AdminPaymentController::class, 'refund'])->middleware('throttle:20,1');
        });
    });

    // ── Public wildcard profile (must be last to avoid shadowing /profile/* routes) ──
    Route::get('/profile/{username}', [ProfileController::class, 'showPublic']);
});
