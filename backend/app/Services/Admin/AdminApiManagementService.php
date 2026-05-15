<?php

namespace App\Services\Admin;

use App\Models\ApiAccessLog;

class AdminApiManagementService
{
    public function logs(array $filters)
    {
        return ApiAccessLog::query()
            ->when($filters['status_code'] ?? null, fn ($q, $code) => $q->where('status_code', (int) $code))
            ->latest('id')
            ->paginate(max(10, min(100, (int) ($filters['per_page'] ?? 20))));
    }

    public function tokenMonitoring(): array
    {
        return [
            'note' => 'Monitor via personal_access_tokens table and Sanctum token pruning job.',
            'checked_at' => now()->toIso8601String(),
        ];
    }

    public function rateLimitingControl(): array
    {
        return [
            'auth-login' => '5/min',
            'auth-general' => '30/min',
            'payment-webhook' => '120/min',
        ];
    }
}
