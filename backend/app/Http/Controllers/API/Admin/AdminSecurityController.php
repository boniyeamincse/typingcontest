<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminSecurityService;
use Illuminate\Http\Request;

class AdminSecurityController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminSecurityService $service)
    {
    }

    public function cheatingUsers()
    {
        return $this->success('Action completed successfully', $this->service->cheatingUsers());
    }

    public function block(Request $request)
    {
        $payload = $request->validate([
            'type' => ['required', 'string', 'in:ip,device,user'],
            'value' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
        ]);

        return $this->success('Action completed successfully', $this->service->block(
            $payload['type'],
            $payload['value'],
            $payload['reason'],
            (int) $request->user()->id,
            $payload['expires_at'] ?? null,
        ));
    }

    public function suspiciousLogins()
    {
        return $this->success('Action completed successfully', $this->service->suspiciousLogins());
    }

    public function rateLimits()
    {
        return $this->success('Action completed successfully', $this->service->rateLimitSnapshot());
    }
}
