<?php

namespace App\Services\Admin;

use App\Models\Leaderboard;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class AdminLeaderboardControlService
{
    public function reset(?string $period = null): int
    {
        $query = Leaderboard::query();

        if ($period) {
            $query->where('period_type', $period);
        }

        return $query->delete();
    }

    public function recalculate(?string $period = null): int
    {
        $rows = Result::query()
            ->selectRaw('user_id, AVG(wpm) as avg_wpm, AVG(accuracy) as avg_accuracy')
            ->groupBy('user_id')
            ->limit(500)
            ->get();

        $count = 0;
        foreach ($rows as $index => $row) {
            Leaderboard::updateOrCreate([
                'scope' => 'global',
                'scope_id' => null,
                'period_type' => $period ?? 'all_time',
                'period_key' => $period ? now()->format('Y-m-d') : 'all',
                'user_id' => $row->user_id,
            ], [
                'rank_position' => $index + 1,
                'wpm' => round((float) $row->avg_wpm, 2),
                'accuracy' => round((float) $row->avg_accuracy, 2),
                'score' => round((float) $row->avg_wpm * ((float) $row->avg_accuracy / 100), 2),
            ]);
            $count++;
        }

        return $count;
    }

    public function removeFakeScores(array $resultIds): int
    {
        return Result::query()->whereIn('id', $resultIds)->delete();
    }

    public function pinTopUsers(array $userIds): int
    {
        return DB::table('users')->whereIn('id', $userIds)->update(['global_rank' => 1]);
    }

    public function export(): array
    {
        return Leaderboard::query()->latest('score')->limit(500)->get()->toArray();
    }
}
