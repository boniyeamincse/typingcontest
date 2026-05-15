<?php

namespace App\Events\Analytics;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalyticsSnapshotGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly string $snapshotDate)
    {
    }
}
