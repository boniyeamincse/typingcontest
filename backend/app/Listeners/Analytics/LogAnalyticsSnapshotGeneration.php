<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\AnalyticsSnapshotGenerated;
use Illuminate\Support\Facades\Log;

class LogAnalyticsSnapshotGeneration
{
    public function handle(AnalyticsSnapshotGenerated $event): void
    {
        Log::info('Daily analytics snapshot generated', [
            'snapshot_date' => $event->snapshotDate,
        ]);
    }
}
