<?php

namespace App\Providers;

use App\Repositories\Auth\EloquentLoginActivityRepository;
use App\Repositories\Auth\EloquentSocialAccountRepository;
use App\Repositories\Auth\EloquentUserRepository;
use App\Repositories\Auth\LoginActivityRepositoryInterface;
use App\Repositories\Auth\SocialAccountRepositoryInterface;
use App\Repositories\Auth\UserRepositoryInterface;
use App\Repositories\Contest\ContestRepositoryInterface;
use App\Repositories\Contest\EloquentContestRepository;
use App\Repositories\Contest\EloquentParticipantRepository;
use App\Repositories\Contest\ParticipantRepositoryInterface;
use App\Repositories\Leaderboard\EloquentLeaderboardRepository;
use App\Repositories\Leaderboard\LeaderboardRepositoryInterface;
use App\Repositories\Payment\EloquentPaymentRepository;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Repositories\Profile\ActivityRepositoryInterface;
use App\Repositories\Profile\EloquentActivityRepository;
use App\Repositories\Profile\EloquentProfileRepository;
use App\Repositories\Profile\EloquentStatisticRepository;
use App\Repositories\Profile\ProfileRepositoryInterface;
use App\Repositories\Profile\StatisticRepositoryInterface;
use App\Repositories\Typing\EloquentTypingInputRepository;
use App\Repositories\Typing\EloquentTypingResultRepository;
use App\Repositories\Typing\EloquentTypingSessionRepository;
use App\Repositories\Typing\TypingInputRepositoryInterface;
use App\Repositories\Typing\TypingResultRepositoryInterface;
use App\Repositories\Typing\TypingSessionRepositoryInterface;
use App\Repositories\Subscription\EloquentSubscriptionRepository;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(SocialAccountRepositoryInterface::class, EloquentSocialAccountRepository::class);
        $this->app->bind(LoginActivityRepositoryInterface::class, EloquentLoginActivityRepository::class);

        // Profile module
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(StatisticRepositoryInterface::class, EloquentStatisticRepository::class);
        $this->app->bind(ActivityRepositoryInterface::class, EloquentActivityRepository::class);

        // Contest module
        $this->app->bind(ContestRepositoryInterface::class, EloquentContestRepository::class);
        $this->app->bind(ParticipantRepositoryInterface::class, EloquentParticipantRepository::class);

        // Typing module
        $this->app->bind(TypingSessionRepositoryInterface::class, EloquentTypingSessionRepository::class);
        $this->app->bind(TypingInputRepositoryInterface::class, EloquentTypingInputRepository::class);
        $this->app->bind(TypingResultRepositoryInterface::class, EloquentTypingResultRepository::class);

        // Leaderboard module
        $this->app->bind(LeaderboardRepositoryInterface::class, EloquentLeaderboardRepository::class);

        // Subscription + Payment modules
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth-login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip().'|'.$request->input('login', 'guest')),
            ];
        });

        RateLimiter::for('auth-general', function (Request $request) {
            return [
                Limit::perMinute(30)->by($request->ip()),
            ];
        });

        RateLimiter::for('payment-webhook', function (Request $request) {
            return [
                Limit::perMinute(120)->by($request->ip()),
            ];
        });
    }
}
