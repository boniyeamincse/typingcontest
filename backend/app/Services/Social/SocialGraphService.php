<?php

namespace App\Services\Social;

use App\Events\Social\FriendRequestSent;
use App\Events\Social\UserFollowed;
use App\Repositories\Social\SocialRepositoryInterface;

class SocialGraphService
{
    public function __construct(private readonly SocialRepositoryInterface $repository)
    {
    }

    public function follow(int $followerId, int $followedId): bool
    {
        $ok = $this->repository->follow($followerId, $followedId);

        if ($ok) {
            event(new UserFollowed($followerId, $followedId));
        }

        return $ok;
    }

    public function unfollow(int $followerId, int $followedId): bool
    {
        return $this->repository->unfollow($followerId, $followedId);
    }

    public function sendFriendRequest(int $senderId, int $receiverId): bool
    {
        $ok = $this->repository->sendFriendRequest($senderId, $receiverId);

        if ($ok) {
            event(new FriendRequestSent($senderId, $receiverId));
        }

        return $ok;
    }

    public function respondFriendRequest(int $requestId, int $receiverId, string $status): bool
    {
        return $this->repository->respondFriendRequest($requestId, $receiverId, $status);
    }
}
