<?php

namespace Tests\Feature;

use App\Events\Rewards\LevelUp;
use App\Events\Rewards\RewardUnlocked;
use App\Events\Social\FriendRequestSent;
use App\Services\Notification\NotificationService;
use Mockery;
use Tests\TestCase;

class EventNotificationWiringTest extends TestCase
{
    public function test_friend_request_sent_event_uses_notification_service_via_listener(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
            ->once()
            ->with(
                ['database', 'websocket'],
                600,
                'New friend request',
                'You received a new friend request.',
                ['sender_id' => 500]
            );

        $this->app->instance(NotificationService::class, $notificationService);

        event(new FriendRequestSent(500, 600));

        $this->assertTrue(true);
    }

    public function test_level_up_event_uses_notification_service_via_listener(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
            ->once()
            ->with(
                ['database', 'websocket'],
                77,
                'Level up!',
                'Congrats, you reached level 4!',
                [
                    'from_level' => 3,
                    'to_level' => 4,
                    'total_xp' => 1000,
                ]
            );

        $this->app->instance(NotificationService::class, $notificationService);

        event(new LevelUp(77, 3, 4, 1000));

        $this->assertTrue(true);
    }

    public function test_reward_unlocked_event_uses_notification_service_via_listener(): void
    {
        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('send')
            ->once()
            ->with(
                ['database', 'websocket'],
                88,
                'Reward unlocked',
                'You unlocked a new reward: streak_30',
                ['days' => 30]
            );

        $this->app->instance(NotificationService::class, $notificationService);

        event(new RewardUnlocked(88, 'streak_30', ['days' => 30]));

        $this->assertTrue(true);
    }
}
