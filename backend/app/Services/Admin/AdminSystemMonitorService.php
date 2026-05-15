<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class AdminSystemMonitorService
{
    public function snapshot(): array
    {
        $redisStatus = 'ok';
        try {
            Redis::ping();
        } catch (\Throwable) {
            $redisStatus = 'down';
        }

        return [
            'cpu_usage' => null,
            'ram_usage' => null,
            'redis_status' => $redisStatus,
            'queue_worker_status' => Cache::has('admin:queue:heartbeat') ? 'running' : 'unknown',
            'websocket_status' => Cache::has('admin:websocket:heartbeat') ? 'running' : 'unknown',
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
