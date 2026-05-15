<?php

namespace App\Providers;

use App\Events\Analytics\AnalyticsSnapshotGenerated;
use App\Events\Auth\UserLoggedIn;
use App\Events\Contest\ScoreUpdated;
use App\Events\Profile\ProfileUpdated;
use App\Events\Rewards\LevelUp;
use App\Events\Rewards\RewardUnlocked;
use App\Events\Social\FriendRequestSent;
use App\Events\Social\UserFollowed;
use App\Events\Typing\TypingFinished;
use App\Listeners\Analytics\LogAnalyticsSnapshotGeneration;
use App\Listeners\Auth\LogLoginActivity;
use App\Listeners\Contest\UpdateLeaderboardOnScoreSubmit;
use App\Listeners\Profile\LogProfileActivity;
use App\Listeners\Rewards\QueueLevelUpNotification;
use App\Listeners\Rewards\QueueRewardUnlockedNotification;
use App\Listeners\Social\CreateFollowFeedItem;
use App\Listeners\Social\QueueFriendRequestNotification;
use App\Listeners\Typing\UpdateLeaderboardOnTypingFinished;
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
        ScoreUpdated::class => [
            UpdateLeaderboardOnScoreSubmit::class,
        ],
        TypingFinished::class => [
            UpdateLeaderboardOnTypingFinished::class,
        ],
        UserFollowed::class => [
            CreateFollowFeedItem::class,
        ],
        FriendRequestSent::class => [
            QueueFriendRequestNotification::class,
        ],
        LevelUp::class => [
            QueueLevelUpNotification::class,
        ],
        RewardUnlocked::class => [
            QueueRewardUnlockedNotification::class,
        ],
        AnalyticsSnapshotGenerated::class => [
            LogAnalyticsSnapshotGeneration::class,
        ],
    ];
}
