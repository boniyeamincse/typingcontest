<?php

namespace App\Repositories\Analytics;

use Illuminate\Support\Facades\DB;

class EloquentAnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function upsertDailySnapshot(array $payload): void
    {
        DB::table('analytics_daily_snapshots')->updateOrInsert(
            ['snapshot_date' => $payload['snapshot_date']],
            [
                'active_users' => $payload['active_users'] ?? 0,
                'matches_played' => $payload['matches_played'] ?? 0,
                'avg_wpm' => $payload['avg_wpm'] ?? 0,
                'revenue' => $payload['revenue'] ?? 0,
                'metadata' => json_encode($payload['metadata'] ?? []),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function getDailySummary(string $date): array
    {
        $row = DB::table('analytics_daily_snapshots')->where('snapshot_date', $date)->first();

        if (!$row) {
            return [
                'snapshot_date' => $date,
                'active_users' => 0,
                'matches_played' => 0,
                'avg_wpm' => 0,
                'revenue' => 0,
                'metadata' => [],
            ];
        }

        return [
            'snapshot_date' => $row->snapshot_date,
            'active_users' => (int) $row->active_users,
            'matches_played' => (int) $row->matches_played,
            'avg_wpm' => (float) $row->avg_wpm,
            'revenue' => (float) $row->revenue,
            'metadata' => $row->metadata ? json_decode($row->metadata, true) : [],
        ];
    }
}
