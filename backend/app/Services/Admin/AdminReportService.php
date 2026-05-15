<?php

namespace App\Services\Admin;

use App\Models\Contest;
use App\Models\Payment;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function analytics(array $filters = []): array
    {
        $from = isset($filters['from']) ? now()->parse($filters['from']) : now()->subDays(30);
        $to = isset($filters['to']) ? now()->parse($filters['to']) : now();

        return [
            'user_growth' => User::query()->whereBetween('created_at', [$from, $to])->count(),
            'revenue' => (float) Payment::query()->where('status', 'paid')->whereBetween('created_at', [$from, $to])->sum('final_amount'),
            'contest_participation' => Result::query()->whereBetween('created_at', [$from, $to])->count(),
            'typing_performance' => [
                'avg_wpm' => round((float) Result::query()->whereBetween('created_at', [$from, $to])->avg('wpm'), 2),
                'avg_accuracy' => round((float) Result::query()->whereBetween('created_at', [$from, $to])->avg('accuracy'), 2),
            ],
            'country_wise' => User::query()
                ->selectRaw('country, COUNT(*) as users')
                ->groupBy('country')
                ->orderByDesc('users')
                ->limit(20)
                ->get()
                ->toArray(),
            'active_contests' => Contest::query()->where('status', Contest::STATUS_ACTIVE)->count(),
        ];
    }

    public function dailyGrowthStats(int $days = 14): array
    {
        $days = max(1, min(90, $days));

        return DB::table('users')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as users')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'users' => (int) $row->users])
            ->all();
    }
}
