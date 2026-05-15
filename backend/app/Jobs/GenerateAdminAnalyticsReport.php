<?php

namespace App\Jobs;

use App\Services\Admin\AdminReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateAdminAnalyticsReport implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $adminId, private readonly array $filters = [])
    {
    }

    public function handle(AdminReportService $service): void
    {
        $report = $service->analytics($this->filters);

        Cache::put('admin:reports:analytics:'.$this->adminId, $report, now()->addMinutes(30));
    }
}
