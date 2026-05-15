<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminDashboardService $service)
    {
    }

    public function overview(Request $request)
    {
        $payload = $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        return $this->success('Action completed successfully', $this->service->overview((int) ($payload['days'] ?? 7)));
    }
}
