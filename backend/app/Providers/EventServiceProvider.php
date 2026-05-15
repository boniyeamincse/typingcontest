<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Events\Profile\ProfileUpdated;
use App\Listeners\Auth\LogLoginActivity;
use App\Listeners\Profile\LogProfileActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            LogLoginActivity::class,
        ],
        ProfileUpdated::class => [
            LogProfileActivity::class,
        ],
    ];
}
