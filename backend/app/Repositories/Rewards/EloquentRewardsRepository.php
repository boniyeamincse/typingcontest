<?php

namespace App\Repositories\Rewards;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class EloquentRewardsRepository implements RewardsRepositoryInterface
{
    public function addXp(int $userId, int $xp, string $source, array $meta = []): int
    {
        DB::table('xp_transactions')->insert([
            'user_id' => $userId,
            'xp' => $xp,
            'source' => $source,
            'meta' => json_encode($meta),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::query()->whereKey($userId)->increment('xp_points', $xp);

        return $this->getUserXp($userId);
    }

    public function getUserXp(int $userId): int
    {
        return (int) User::query()->whereKey($userId)->value('xp_points');
    }

    public function updateStreak(int $userId): array
    {
        $row = DB::table('user_statistics')->where('user_id', $userId)->first();

        if (!$row) {
            DB::table('user_statistics')->insert([
                'user_id' => $userId,
                'typing_streak' => 1,
                'longest_streak' => 1,
                'streak_last_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['streak' => 1, 'longest_streak' => 1];
        }

        $lastDate = $row->streak_last_date ? \Carbon\Carbon::parse($row->streak_last_date) : null;
        $today = now()->startOfDay();

        if ($lastDate && $lastDate->isSameDay($today)) {
            return ['streak' => (int) $row->typing_streak, 'longest_streak' => (int) $row->longest_streak];
        }

        $isConsecutive = $lastDate && $lastDate->copy()->addDay()->isSameDay($today);
        $newStreak = $isConsecutive ? ((int) $row->typing_streak + 1) : 1;
        $newLongest = max((int) $row->longest_streak, $newStreak);

        DB::table('user_statistics')->where('user_id', $userId)->update([
            'typing_streak' => $newStreak,
            'longest_streak' => $newLongest,
            'streak_last_date' => $today->toDateString(),
            'updated_at' => now(),
        ]);

        return ['streak' => $newStreak, 'longest_streak' => $newLongest];
    }

    public function resetStreak(int $userId): void
    {
        DB::table('user_statistics')->where('user_id', $userId)->update([
            'typing_streak' => 0,
            'updated_at' => now(),
        ]);
    }
}
