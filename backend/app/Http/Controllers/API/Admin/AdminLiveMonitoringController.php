<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminLiveMonitorService;

class AdminLiveMonitoringController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminLiveMonitorService $service)
    {
    }

    public function show(int $contestId)
    {
        return $this->success('Action completed successfully', $this->service->contestSnapshot($contestId));
    }

    public function forceStop(int $contestId)
    {
        $this->service->forceStop($contestId);

        return $this->success('Action completed successfully', []);
    }
}
