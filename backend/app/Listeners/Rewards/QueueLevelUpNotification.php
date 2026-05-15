<?php

namespace App\Listeners\Rewards;

use App\Events\Rewards\LevelUp;
use App\Services\Notification\NotificationService;

class QueueLevelUpNotification
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(LevelUp $event): void
    {
        $this->notificationService->send(
            ['database', 'websocket'],
            $event->userId,
            'Level up!',
            'Congrats, you reached level '.$event->toLevel.'!',
            [
                'from_level' => $event->fromLevel,
                'to_level' => $event->toLevel,
                'total_xp' => $event->totalXp,
            ]
        );
    }
}
