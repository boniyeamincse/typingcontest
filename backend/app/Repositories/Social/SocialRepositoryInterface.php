<?php

namespace App\Repositories\Social;

use Illuminate\Support\Collection;

interface SocialRepositoryInterface
{
    public function follow(int $followerId, int $followedId): bool;

    public function unfollow(int $followerId, int $followedId): bool;

    public function sendFriendRequest(int $senderId, int $receiverId): bool;

    public function respondFriendRequest(int $requestId, int $receiverId, string $status): bool;

    public function feedForUser(int $userId, int $limit = 20): Collection;
}
