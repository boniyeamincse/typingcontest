<?php

namespace App\Listeners\Social;

use App\Events\Social\UserFollowed;
use Illuminate\Support\Facades\DB;

class CreateFollowFeedItem
{
    public function handle(UserFollowed $event): void
    {
        DB::table('social_feed_items')->insert([
            'user_id' => $event->followedId,
            'actor_user_id' => $event->followerId,
            'type' => 'user_followed',
            'title' => 'New follower',
            'payload' => json_encode(['follower_id' => $event->followerId]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
