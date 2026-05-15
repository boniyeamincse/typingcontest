<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Services\Admin\AdminApiManagementService;
use App\Services\Admin\AdminSystemMonitorService;

class AdminSystemController extends Controller
{
    use RespondsWithApi;

    public function __construct(
        private readonly AdminSystemMonitorService $monitorService,
        private readonly AdminApiManagementService $apiService,
    ) {
    }

    public function monitoring()
    {
        return $this->success('Action completed successfully', $this->monitorService->snapshot());
    }

    public function activityLogs()
    {
        return $this->success('Action completed successfully', AdminActivityLog::query()->with('admin:id,name,email')->latest('id')->paginate(50));
    }

    public function apiLogs()
    {
        return $this->success('Action completed successfully', $this->apiService->logs(request()->all()));
    }
}
