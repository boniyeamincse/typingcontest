<?php

namespace App\Listeners\Social;

use App\Events\Social\FriendRequestSent;
use App\Services\Notification\NotificationService;

class QueueFriendRequestNotification
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(FriendRequestSent $event): void
    {
        $this->notificationService->send(
            ['database', 'websocket'],
            $event->receiverId,
            'New friend request',
            'You received a new friend request.',
            ['sender_id' => $event->senderId]
        );
    }
}
