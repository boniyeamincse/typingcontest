<?php

namespace App\Services\Admin;

use App\Models\AdminActivityLog;
use App\Models\User;

class AdminAuditService
{
    public function log(User $admin, string $module, string $action, mixed $target = null, array $meta = []): void
    {
        AdminActivityLog::create([
            'admin_id' => $admin->id,
            'module' => $module,
            'action' => $action,
            'target_type' => is_object($target) ? $target::class : null,
            'target_id' => is_object($target) && isset($target->id) ? (int) $target->id : null,
            'meta' => $meta,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
        ]);
    }
}
