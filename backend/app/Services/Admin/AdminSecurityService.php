<?php

namespace App\Services\Admin;

use App\Models\AntiCheatLog;
use App\Models\LoginActivity;
use App\Models\SecurityBlock;

class AdminSecurityService
{
    public function cheatingUsers()
    {
        return AntiCheatLog::query()->latest('id')->paginate(30);
    }

    public function block(string $type, string $value, string $reason, int $adminId, ?string $expiresAt = null): SecurityBlock
    {
        return SecurityBlock::updateOrCreate([
            'type' => $type,
            'value' => $value,
        ], [
            'reason' => $reason,
            'created_by' => $adminId,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }

    public function suspiciousLogins()
    {
        return LoginActivity::query()
            ->where('successful', false)
            ->orWhere('ip_address', 'like', '10.%')
            ->latest('id')
            ->paginate(30);
    }

    public function rateLimitSnapshot(): array
    {
        return [
            'status' => 'ok',
            'note' => 'Use Laravel rate limiter keys and Telescope for per-endpoint depth.',
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
