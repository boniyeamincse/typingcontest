<?php

namespace App\Repositories\Profile;

use App\Models\User;
use App\Models\UserStatistic;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentStatisticRepository implements StatisticRepositoryInterface
{
    public function getOrCreate(User $user): UserStatistic
    {
        return UserStatistic::firstOrCreate(
            ['user_id' => $user->id],
            [
                'avg_wpm'       => 0,
                'highest_wpm'   => 0,
                'avg_accuracy'  => 0,
                'total_matches' => 0,
                'total_wins'    => 0,
                'typing_streak' => 0,
                'longest_streak' => 0,
                'typing_minutes' => 0,
                'total_points'  => 0,
            ]
        );
    }

    public function recalculate(User $user): UserStatistic
    {
        $stats = DB::table('contest_participants')
            ->where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_matches,
                COALESCE(AVG(wpm), 0) as avg_wpm,
                COALESCE(MAX(wpm), 0) as highest_wpm,
                COALESCE(AVG(accuracy), 0) as avg_accuracy,
                COALESCE(SUM(score), 0) as total_points,
                SUM(CASE WHEN rank = 1 THEN 1 ELSE 0 END) as total_wins
            ')
            ->first();

        $statistic = $this->getOrCreate($user);

        $statistic->update([
            'avg_wpm'       => (int) round($stats->avg_wpm ?? 0),
            'highest_wpm'   => (int) ($stats->highest_wpm ?? 0),
            'avg_accuracy'  => round($stats->avg_accuracy ?? 0, 2),
            'total_matches' => (int) ($stats->total_matches ?? 0),
            'total_wins'    => (int) ($stats->total_wins ?? 0),
            'total_points'  => (int) ($stats->total_points ?? 0),
        ]);

        // Recalculate streak
        $this->recalculateStreak($user, $statistic);

        return $statistic->fresh();
    }

    public function updateRanks(User $user, array $ranks): void
    {
        $statistic = $this->getOrCreate($user);
        $statistic->update(array_filter([
            'global_rank'  => $ranks['global'] ?? null,
            'weekly_rank'  => $ranks['weekly'] ?? null,
            'monthly_rank' => $ranks['monthly'] ?? null,
            'country_rank' => $ranks['country'] ?? null,
        ], fn($v) => $v !== null));
    }

    private function recalculateStreak(User $user, UserStatistic $statistic): void
    {
        $participationDates = DB::table('contest_participants')
            ->where('user_id', $user->id)
            ->selectRaw('DATE(COALESCE(submitted_at, created_at)) as participation_date')
            ->distinct()
            ->orderByDesc('participation_date')
            ->pluck('participation_date')
            ->map(fn($d) => Carbon::parse($d)->startOfDay())
            ->values();

        if ($participationDates->isEmpty()) {
            return;
        }

        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();
        $streak    = 0;

        // If last participation was not today or yesterday, streak is 0
        $lastDate = $participationDates->first();
        if (!$lastDate->eq($today) && !$lastDate->eq($yesterday)) {
            $statistic->update(['typing_streak' => 0, 'streak_last_date' => $lastDate->toDateString()]);
            return;
        }

        $streak = 1;
        $expected = $lastDate->copy()->subDay();

        foreach ($participationDates->slice(1) as $date) {
            if ($date->eq($expected)) {
                $streak++;
                $expected->subDay();
            } else {
                break;
            }
        }

        $longestStreak = max($streak, $statistic->longest_streak ?? 0);
        $statistic->update([
            'typing_streak'    => $streak,
            'longest_streak'   => $longestStreak,
            'streak_last_date' => $lastDate->toDateString(),
        ]);
    }
}
