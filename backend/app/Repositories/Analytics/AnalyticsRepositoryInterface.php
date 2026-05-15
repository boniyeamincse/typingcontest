<?php

namespace App\Repositories\Analytics;

interface AnalyticsRepositoryInterface
{
    public function upsertDailySnapshot(array $payload): void;

    public function getDailySummary(string $date): array;
}
