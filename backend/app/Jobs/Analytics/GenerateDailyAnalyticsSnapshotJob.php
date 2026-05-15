<?php

namespace App\Jobs\Analytics;

use App\Services\Analytics\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateDailyAnalyticsSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'analytics';

    public function __construct(private readonly string $snapshotDate)
    {
    }

    public function handle(AnalyticsService $analyticsService): void
    {
        $activeUsers = DB::table('results')->whereDate('created_at', $this->snapshotDate)->distinct('user_id')->count('user_id');
        $matchesPlayed = DB::table('results')->whereDate('created_at', $this->snapshotDate)->count();
        $avgWpm = (float) DB::table('results')->whereDate('created_at', $this->snapshotDate)->avg('wpm');

        $analyticsService->upsertDailySnapshot([
            'snapshot_date' => $this->snapshotDate,
            'active_users' => $activeUsers,
            'matches_played' => $matchesPlayed,
            'avg_wpm' => $avgWpm,
            'revenue' => 0,
            'metadata' => [],
        ]);
    }
}
