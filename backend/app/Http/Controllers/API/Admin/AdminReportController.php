<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateAdminAnalyticsReport;
use App\Services\Admin\AdminReportService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminReportService $service)
    {
    }

    public function index(Request $request)
    {
        $payload = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        return $this->success('Action completed successfully', [
            'analytics' => $this->service->analytics($payload),
            'daily_growth' => $this->service->dailyGrowthStats((int) ($payload['days'] ?? 14)),
        ]);
    }

    public function queue(Request $request)
    {
        $payload = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        GenerateAdminAnalyticsReport::dispatch($request->user()->id, $payload);

        return $this->success('Action completed successfully', ['queued' => true]);
    }
}
