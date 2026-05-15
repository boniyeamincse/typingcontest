<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminApiManagementService;
use App\Services\Admin\AdminSystemMonitorService;

class AdminModulePlaceholderController extends Controller
{
    use RespondsWithApi;

    public function __construct(
        private readonly AdminSystemMonitorService $monitorService,
        private readonly AdminApiManagementService $apiService,
    ) {
    }

    public function advertisementsOverview()
    {
        return $this->success('Advertisement placeholder data fetched successfully', [
            'metrics' => [
                'active_banners' => 0,
                'sponsored_contests' => 0,
                'monthly_impressions' => 0,
                'monthly_revenue' => 0,
            ],
            'placements' => [
                ['name' => 'Homepage Hero', 'status' => 'available'],
                ['name' => 'Contest Sidebar', 'status' => 'available'],
                ['name' => 'Leaderboard Footer', 'status' => 'available'],
            ],
            'next_actions' => [
                'Create ad inventory model',
                'Add campaign CRUD APIs',
                'Connect impression and click tracking',
            ],
        ]);
    }

    public function sponsorsOverview()
    {
        return $this->success('Sponsor placeholder data fetched successfully', [
            'metrics' => [
                'total_sponsors' => 0,
                'active_campaigns' => 0,
                'sponsored_events' => 0,
            ],
            'sponsor_tiers' => ['Gold', 'Silver', 'Bronze'],
            'next_actions' => [
                'Create sponsor table and repository',
                'Add sponsor onboarding workflow',
                'Attach sponsors to contests and events',
            ],
        ]);
    }

    public function apiManagementOverview()
    {
        return $this->success('API management data fetched successfully', [
            'recent_logs' => $this->apiService->logs(['per_page' => 10]),
            'rate_limits' => $this->apiService->rateLimitingControl(),
            'token_monitoring' => $this->apiService->tokenMonitoring(),
        ]);
    }

    public function backupMaintenanceOverview()
    {
        return $this->success('Backup and maintenance placeholder data fetched successfully', [
            'metrics' => [
                'last_backup_at' => null,
                'backup_status' => 'not_configured',
                'maintenance_mode' => app()->isDownForMaintenance(),
            ],
            'operations' => [
                'Database Backup',
                'Restore System',
                'Cache Clear',
                'Maintenance Mode',
            ],
            'next_actions' => [
                'Integrate scheduled backup command',
                'Add restore safety checks',
                'Add backup storage health monitor',
            ],
        ]);
    }

    public function systemMonitoringOverview()
    {
        return $this->success('System monitoring data fetched successfully', [
            'snapshot' => $this->monitorService->snapshot(),
            'queue_jobs' => [
                'driver' => config('queue.default'),
                'status' => 'placeholder',
            ],
            'notes' => [
                'CPU and RAM are placeholders until host-level probes are integrated.',
            ],
        ]);
    }
}
