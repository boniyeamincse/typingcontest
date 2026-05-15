<?php

namespace App\Services\Admin;

use App\Events\AdminNotificationBroadcasted;
use App\Models\AdminNotification;

class AdminNotificationService
{
    public function send(array $payload, int $adminId): AdminNotification
    {
        $notification = AdminNotification::create([
            'title' => $payload['title'],
            'message' => $payload['message'],
            'channels' => $payload['channels'] ?? ['in_app'],
            'audience' => $payload['audience'] ?? ['all'],
            'created_by' => $adminId,
            'sent_at' => now(),
        ]);

        broadcast(new AdminNotificationBroadcasted($notification))->toOthers();

        return $notification;
    }
}
