<?php

namespace App\Repositories\Admin;

interface AdminDashboardRepositoryInterface
{
    public function summary(): array;

    public function dailyGrowth(int $days = 7): array;

    public function systemHealth(): array;
}
