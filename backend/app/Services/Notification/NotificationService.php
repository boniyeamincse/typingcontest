<?php

namespace App\Services\Notification;

use App\Jobs\Notification\DispatchNotificationJob;

class NotificationService
{
    public function send(array $channels, int $userId, string $title, string $message, array $payload = []): void
    {
        DispatchNotificationJob::dispatch($channels, $userId, $title, $message, $payload);
    }
}
