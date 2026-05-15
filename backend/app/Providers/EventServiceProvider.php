<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Listeners\Auth\LogLoginActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            LogLoginActivity::class,
        ],
    ];
}
