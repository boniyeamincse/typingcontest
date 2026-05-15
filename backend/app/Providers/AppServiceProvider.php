<?php

namespace App\Providers;

use App\Repositories\Auth\EloquentLoginActivityRepository;
use App\Repositories\Auth\EloquentSocialAccountRepository;
use App\Repositories\Auth\EloquentUserRepository;
use App\Repositories\Auth\LoginActivityRepositoryInterface;
use App\Repositories\Auth\SocialAccountRepositoryInterface;
use App\Repositories\Auth\UserRepositoryInterface;
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
    }
}
