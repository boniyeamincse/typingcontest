<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Repositories\Auth\LoginActivityRepositoryInterface;

class LogLoginActivity
{
    public function __construct(private readonly LoginActivityRepositoryInterface $activityRepository)
    {
    }

    public function handle(UserLoggedIn $event): void
    {
        $this->activityRepository->create($event->user, [
            'login_identifier' => $event->loginIdentifier,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'device_name' => $event->deviceName,
            'status' => 'success',
            'logged_in_at' => now(),
        ]);
    }
}
