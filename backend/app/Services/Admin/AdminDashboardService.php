<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminDashboardRepositoryInterface;

class AdminDashboardService
{
    public function __construct(private readonly AdminDashboardRepositoryInterface $repository)
    {
    }

    public function overview(int $days = 7): array
    {
        return [
            'summary' => $this->repository->summary(),
            'daily_growth' => $this->repository->dailyGrowth($days),
            'system_health' => $this->repository->systemHealth(),
        ];
    }
}
