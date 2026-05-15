<?php

namespace App\Services\Social;

use App\Repositories\Social\SocialRepositoryInterface;
use Illuminate\Support\Collection;

class FeedService
{
    public function __construct(private readonly SocialRepositoryInterface $repository)
    {
    }

    public function getUserFeed(int $userId, int $limit = 20): Collection
    {
        return $this->repository->feedForUser($userId, $limit);
    }
}
