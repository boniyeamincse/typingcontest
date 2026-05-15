<?php

namespace App\Repositories\Admin;

use App\Models\Contest;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class EloquentAdminDashboardRepository implements AdminDashboardRepositoryInterface
{
    public function summary(): array
    {
        return Cache::remember('admin:dashboard:summary', 30, function (): array {
            $today = now()->startOfDay();

            return [
                'total_users' => User::count(),
                'active_users' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
                'pro_users' => User::whereIn('plan_type', ['pro', 'vip'])->count(),
                'revenue' => (float) Payment::where('status', 'paid')->sum('final_amount'),
                'active_contests' => Contest::where('status', Contest::STATUS_ACTIVE)->count(),
                'live_players' => DB::table('contest_sessions')->whereNull('finished_at')->count(),
                'today_new_users' => User::where('created_at', '>=', $today)->count(),
            ];
        });
    }

    public function dailyGrowth(int $days = 7): array
    {
        $days = max(1, min(60, $days));

        return User::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as users')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'users' => (int) $row->users])
            ->all();
    }

    public function systemHealth(): array
    {
        $redisOk = true;

        try {
            Redis::ping();
        } catch (\Throwable) {
            $redisOk = false;
        }

        return [
            'app' => 'ok',
            'database' => 'ok',
            'redis' => $redisOk ? 'ok' : 'down',
            'queue' => Cache::has('admin:queue:heartbeat') ? 'ok' : 'unknown',
            'websocket' => Cache::has('admin:websocket:heartbeat') ? 'ok' : 'unknown',
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
