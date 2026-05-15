<?php

namespace App\Listeners\Rewards;

use App\Events\Rewards\RewardUnlocked;
use App\Services\Notification\NotificationService;

class QueueRewardUnlockedNotification
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(RewardUnlocked $event): void
    {
        $this->notificationService->send(
            ['database', 'websocket'],
            $event->userId,
            'Reward unlocked',
            'You unlocked a new reward: '.$event->badgeCode,
            $event->payload
        );
    }
}
