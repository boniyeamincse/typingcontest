<?php

namespace App\Repositories\Social;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentSocialRepository implements SocialRepositoryInterface
{
    public function follow(int $followerId, int $followedId): bool
    {
        if ($followerId === $followedId) {
            return false;
        }

        return DB::table('user_follows')->updateOrInsert(
            ['follower_id' => $followerId, 'followed_id' => $followedId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function unfollow(int $followerId, int $followedId): bool
    {
        return DB::table('user_follows')
            ->where('follower_id', $followerId)
            ->where('followed_id', $followedId)
            ->delete() > 0;
    }

    public function sendFriendRequest(int $senderId, int $receiverId): bool
    {
        if ($senderId === $receiverId) {
            return false;
        }

        return DB::table('friend_requests')->updateOrInsert(
            ['sender_id' => $senderId, 'receiver_id' => $receiverId],
            ['status' => 'pending', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function respondFriendRequest(int $requestId, int $receiverId, string $status): bool
    {
        return DB::table('friend_requests')
            ->where('id', $requestId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->update([
                'status' => $status,
                'responded_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }

    public function feedForUser(int $userId, int $limit = 20): Collection
    {
        return DB::table('social_feed_items')
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->orWhereNull('user_id');
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
