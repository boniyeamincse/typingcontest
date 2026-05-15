<?php

use App\Http\Controllers\API\Admin\AdminAuthController;
use App\Http\Controllers\API\Admin\AdminBadgeRewardController;
use App\Http\Controllers\API\Admin\AdminCmsController;
use App\Http\Controllers\API\Admin\AdminContestController;
use App\Http\Controllers\API\Admin\AdminContentController;
use App\Http\Controllers\API\Admin\AdminDashboardController;
use App\Http\Controllers\API\Admin\AdminLeaderboardController;
use App\Http\Controllers\API\Admin\AdminLiveMonitoringController;
use App\Http\Controllers\API\Admin\AdminNotificationController;
use App\Http\Controllers\API\Admin\AdminPaymentController as DashboardAdminPaymentController;
use App\Http\Controllers\API\Admin\AdminReportController;
use App\Http\Controllers\API\Admin\AdminRoleController;
use App\Http\Controllers\API\Admin\AdminSecurityController;
use App\Http\Controllers\API\Admin\AdminSubscriptionController as DashboardAdminSubscriptionController;
use App\Http\Controllers\API\Admin\AdminSupportController;
use App\Http\Controllers\API\Admin\AdminSystemController;
use App\Http\Controllers\API\Admin\AdminUserController;
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

    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:auth-login');

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

        // Existing admin contest + subscription/payment controls
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

            Route::get('/admin/coupons', [AdminSubscriptionController::class, 'coupons']);
            Route::post('/admin/coupons', [AdminSubscriptionController::class, 'createCoupon'])->middleware('throttle:30,1');
            Route::put('/admin/coupons/{coupon}', [AdminSubscriptionController::class, 'updateCoupon'])->middleware('throttle:30,1');
            Route::post('/admin/coupons/{coupon}/toggle', [AdminSubscriptionController::class, 'toggleCoupon'])->middleware('throttle:30,1');

            Route::post('/admin/subscriptions/override', [AdminSubscriptionController::class, 'override'])->middleware('throttle:20,1');
            Route::get('/admin/reports/subscriptions-payments', [AdminPaymentController::class, 'report']);
            Route::get('/admin/reports/subscriptions-payments/export', [AdminPaymentController::class, 'export']);
            Route::post('/admin/payments/{paymentIntentId}/refund', [AdminPaymentController::class, 'refund'])->middleware('throttle:20,1');
        });

        // New production admin dashboard APIs
        Route::prefix('admin')
            ->middleware(['throttle:admin-api', 'role_or_permission:super_admin|contest_admin|user_moderator|support_admin|content_manager|admin'])
            ->group(function () {
                Route::post('/logout', [AdminAuthController::class, 'logout']);
                Route::get('/dashboard/overview', [AdminDashboardController::class, 'overview']);

                Route::get('/users', [AdminUserController::class, 'index'])->middleware('admin.module:users');
                Route::post('/users/ban', [AdminUserController::class, 'ban'])->middleware('admin.module:users');
                Route::post('/users/unban', [AdminUserController::class, 'unban'])->middleware('admin.module:users');
                Route::post('/users/suspend', [AdminUserController::class, 'suspend'])->middleware('admin.module:users');
                Route::post('/users/reset-password', [AdminUserController::class, 'resetPassword'])->middleware('admin.module:users');
                Route::get('/users/suspicious', [AdminUserController::class, 'suspicious'])->middleware('admin.module:users');

                Route::get('/contests', [AdminContestController::class, 'index'])->middleware('admin.module:contests');
                Route::post('/contests/{contest}/stop', [AdminContestController::class, 'stop'])->middleware('admin.module:contests');
                Route::post('/contests/{contest}/clone', [AdminContestController::class, 'cloneContest'])->middleware('admin.module:contests');
                Route::get('/contests/{contest}/analytics', [AdminContestController::class, 'analytics'])->middleware('admin.module:contests');

                Route::get('/live/contests/{contestId}', [AdminLiveMonitoringController::class, 'show'])->middleware('admin.module:contests');
                Route::post('/live/contests/{contestId}/force-stop', [AdminLiveMonitoringController::class, 'forceStop'])->middleware('admin.module:contests');

                Route::get('/content/paragraphs', [AdminContentController::class, 'index'])->middleware('admin.module:content');
                Route::post('/content/paragraphs', [AdminContentController::class, 'store'])->middleware('admin.module:content');
                Route::put('/content/paragraphs/{typingText}', [AdminContentController::class, 'update'])->middleware('admin.module:content');
                Route::delete('/content/paragraphs/{typingText}', [AdminContentController::class, 'destroy'])->middleware('admin.module:content');

                Route::get('/subscriptions', [DashboardAdminSubscriptionController::class, 'index'])->middleware('admin.module:subscriptions');
                Route::get('/subscriptions/plans', [DashboardAdminSubscriptionController::class, 'plans'])->middleware('admin.module:subscriptions');
                Route::post('/subscriptions/change-plan', [DashboardAdminSubscriptionController::class, 'upgradeDowngrade'])->middleware('admin.module:subscriptions');
                Route::post('/subscriptions/cancel', [DashboardAdminSubscriptionController::class, 'cancel'])->middleware('admin.module:subscriptions');

                Route::get('/payments', [DashboardAdminPaymentController::class, 'index'])->middleware('admin.module:payments');
                Route::post('/payments/{payment}/verify', [DashboardAdminPaymentController::class, 'verify'])->middleware('admin.module:payments');
                Route::get('/payments/{payment}/fraud-check', [DashboardAdminPaymentController::class, 'fraudCheck'])->middleware('admin.module:payments');

                Route::get('/badges', [AdminBadgeRewardController::class, 'index'])->middleware('admin.module:content');
                Route::post('/badges', [AdminBadgeRewardController::class, 'store'])->middleware('admin.module:content');
                Route::post('/badges/assign', [AdminBadgeRewardController::class, 'assign'])->middleware('admin.module:content');
                Route::post('/badges/xp-rule', [AdminBadgeRewardController::class, 'xpRule'])->middleware('admin.module:content');

                Route::post('/leaderboard/reset', [AdminLeaderboardController::class, 'reset'])->middleware('admin.module:leaderboard');
                Route::post('/leaderboard/recalculate', [AdminLeaderboardController::class, 'recalculate'])->middleware('admin.module:leaderboard');
                Route::post('/leaderboard/remove-fake', [AdminLeaderboardController::class, 'removeFake'])->middleware('admin.module:leaderboard');
                Route::post('/leaderboard/pin-top', [AdminLeaderboardController::class, 'pinTop'])->middleware('admin.module:leaderboard');
                Route::get('/leaderboard/export', [AdminLeaderboardController::class, 'export'])->middleware('admin.module:leaderboard');

                Route::get('/reports', [AdminReportController::class, 'index'])->middleware('admin.module:reports');
                Route::post('/reports/queue', [AdminReportController::class, 'queue'])->middleware('admin.module:reports');

                Route::get('/security/cheating-users', [AdminSecurityController::class, 'cheatingUsers'])->middleware('admin.module:security');
                Route::post('/security/blocks', [AdminSecurityController::class, 'block'])->middleware('admin.module:security');
                Route::get('/security/suspicious-logins', [AdminSecurityController::class, 'suspiciousLogins'])->middleware('admin.module:security');
                Route::get('/security/rate-limits', [AdminSecurityController::class, 'rateLimits'])->middleware('admin.module:security');

                Route::get('/support/tickets', [AdminSupportController::class, 'index'])->middleware('admin.module:support');
                Route::post('/support/tickets', [AdminSupportController::class, 'store'])->middleware('admin.module:support');
                Route::post('/support/tickets/{ticket}/respond', [AdminSupportController::class, 'respond'])->middleware('admin.module:support');
                Route::post('/support/tickets/{ticket}/close', [AdminSupportController::class, 'close'])->middleware('admin.module:support');
                Route::post('/support/tickets/{ticket}/escalate', [AdminSupportController::class, 'escalate'])->middleware('admin.module:support');

                Route::get('/cms/pages', [AdminCmsController::class, 'pages'])->middleware('admin.module:content');
                Route::post('/cms/pages', [AdminCmsController::class, 'savePage'])->middleware('admin.module:content');
                Route::get('/cms/banners', [AdminCmsController::class, 'banners'])->middleware('admin.module:content');
                Route::post('/cms/banners', [AdminCmsController::class, 'saveBanner'])->middleware('admin.module:content');

                Route::post('/notifications/send', [AdminNotificationController::class, 'send'])->middleware('admin.module:notifications');

                Route::get('/system/monitoring', [AdminSystemController::class, 'monitoring'])->middleware('admin.module:system');
                Route::get('/activity-logs', [AdminSystemController::class, 'activityLogs'])->middleware('admin.module:system');
                Route::get('/api/logs', [AdminSystemController::class, 'apiLogs'])->middleware('admin.module:system');

                Route::get('/roles', [AdminRoleController::class, 'index'])->middleware('admin.module:roles');
                Route::post('/roles/assign', [AdminRoleController::class, 'assign'])->middleware('admin.module:roles');
            });
    });

    // ── Public wildcard profile (must be last to avoid shadowing /profile/* routes) ──
    Route::get('/profile/{username}', [ProfileController::class, 'showPublic']);
});
