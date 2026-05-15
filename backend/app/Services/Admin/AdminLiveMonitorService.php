<?php

namespace App\Services\Admin;

use App\Models\Contest;
use App\Models\ContestSession;
use App\Models\Result;
use App\Models\TypingResult;

class AdminLiveMonitorService
{
    public function contestSnapshot(int $contestId): array
    {
        $contest = Contest::query()->findOrFail($contestId);

        return [
            'contest' => $contest,
            'live_participants' => ContestSession::query()
                ->where('contest_id', $contestId)
                ->whereNull('finished_at')
                ->count(),
            'active_sessions' => ContestSession::query()
                ->where('contest_id', $contestId)
                ->whereNull('finished_at')
                ->latest('id')
                ->limit(50)
                ->get(),
            'live_leaderboard' => Result::query()
                ->where('contest_id', $contestId)
                ->orderByDesc('wpm')
                ->orderByDesc('accuracy')
                ->limit(20)
                ->get(),
            'live_wpm_updates' => TypingResult::query()
                ->where('contest_id', $contestId)
                ->latest('id')
                ->limit(30)
                ->get(),
        ];
    }

    public function forceStop(int $contestId): void
    {
        ContestSession::query()
            ->where('contest_id', $contestId)
            ->whereNull('finished_at')
            ->update([
                'finished_at' => now(),
                'status' => 'aborted',
            ]);
    }
}
