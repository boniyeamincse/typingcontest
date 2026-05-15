<?php

namespace App\Services\Analytics;

use App\Events\Analytics\AnalyticsSnapshotGenerated;
use App\Repositories\Analytics\AnalyticsRepositoryInterface;

class AnalyticsService
{
    public function __construct(private readonly AnalyticsRepositoryInterface $repository)
    {
    }

    public function upsertDailySnapshot(array $payload): void
    {
        $this->repository->upsertDailySnapshot($payload);

        event(new AnalyticsSnapshotGenerated($payload['snapshot_date']));
    }

    public function getDailySummary(string $date): array
    {
        return $this->repository->getDailySummary($date);
    }
}
