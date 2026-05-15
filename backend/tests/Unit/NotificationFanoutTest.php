<?php

namespace Tests\Unit;

use App\Events\Rewards\LevelUp;
use App\Events\Rewards\RewardUnlocked;
use App\Events\Social\FriendRequestSent;
use App\Events\Social\UserFollowed;
use App\Listeners\Rewards\QueueLevelUpNotification;
use App\Listeners\Rewards\QueueRewardUnlockedNotification;
use App\Listeners\Social\QueueFriendRequestNotification;
use App\Repositories\Social\SocialRepositoryInterface;
use App\Services\Notification\NotificationService;
use App\Services\Social\SocialGraphService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationFanoutTest extends TestCase
{
    public function test_social_graph_service_dispatches_friend_request_sent_event(): void
    {
        Event::fake([FriendRequestSent::class]);

        $repo = $this->createMock(SocialRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('sendFriendRequest')
            ->with(101, 202)
            ->willReturn(true);

        $service = new SocialGraphService($repo);

        $ok = $service->sendFriendRequest(101, 202);

        $this->assertTrue($ok);
        Event::assertDispatched(FriendRequestSent::class, function (FriendRequestSent $event): bool {
            return $event->senderId === 101 && $event->receiverId === 202;
        });
    }

    public function test_social_graph_service_dispatches_user_followed_event(): void
    {
        Event::fake([UserFollowed::class]);

        $repo = $this->createMock(SocialRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('follow')
            ->with(11, 22)
            ->willReturn(true);

        $service = new SocialGraphService($repo);

        $ok = $service->follow(11, 22);

        $this->assertTrue($ok);
        Event::assertDispatched(UserFollowed::class, function (UserFollowed $event): bool {
            return $event->followerId === 11 && $event->followedId === 22;
        });
    }

    public function test_friend_request_listener_calls_notification_service_with_expected_payload(): void
    {
        $notificationService = $this->createMock(NotificationService::class);
        $notificationService->expects($this->once())
            ->method('send')
            ->with(
                ['database', 'websocket'],
                600,
                'New friend request',
                'You received a new friend request.',
                ['sender_id' => 500]
            );

        $listener = new QueueFriendRequestNotification($notificationService);
        $listener->handle(new FriendRequestSent(500, 600));
    }

    public function test_level_up_listener_calls_notification_service_with_expected_payload(): void
    {
        $notificationService = $this->createMock(NotificationService::class);
        $notificationService->expects($this->once())
            ->method('send')
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

        $listener = new QueueLevelUpNotification($notificationService);
        $listener->handle(new LevelUp(77, 3, 4, 1000));
    }

    public function test_reward_unlocked_listener_calls_notification_service_with_expected_payload(): void
    {
        $notificationService = $this->createMock(NotificationService::class);
        $notificationService->expects($this->once())
            ->method('send')
            ->with(
                ['database', 'websocket'],
                88,
                'Reward unlocked',
                'You unlocked a new reward: streak_30',
                ['days' => 30]
            );

        $listener = new QueueRewardUnlockedNotification($notificationService);
        $listener->handle(new RewardUnlocked(88, 'streak_30', ['days' => 30]));
    }

}
