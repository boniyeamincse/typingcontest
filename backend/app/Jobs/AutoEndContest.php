<?php

namespace App\Jobs;

use App\Models\Contest;
use App\Services\Contest\ContestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoEndContest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(private readonly int $contestId) {}

    public function handle(ContestService $contestService): void
    {
        $contest = Contest::find($this->contestId);

        if (!$contest || $contest->status !== Contest::STATUS_ACTIVE) {
            return;
        }

        try {
            $contestService->end($contest);
        } catch (\Throwable $e) {
            Log::error("AutoEndContest failed for contest #{$this->contestId}: " . $e->getMessage());
        }
    }
}
