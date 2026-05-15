<?php

namespace App\Services\Typing;

use App\Models\TypingSession;
use Illuminate\Support\Facades\Cache;

class TypingRealtimeService
{
    public function storePartialProgress(TypingSession $session, array $data, int $ttlSeconds = 3600): void
    {
        try {
            Cache::store('redis')->put($this->progressKey($session->id), $data, $ttlSeconds);
        } catch (\Throwable $e) {
            report($e);
            Cache::store()->put($this->progressKey($session->id), $data, $ttlSeconds);
        }
    }

    public function getPartialProgress(TypingSession $session): array
    {
        try {
            return Cache::store('redis')->get($this->progressKey($session->id), []);
        } catch (\Throwable $e) {
            report($e);
            return Cache::store()->get($this->progressKey($session->id), []);
        }
    }

    public function clearPartialProgress(TypingSession $session): void
    {
        try {
            Cache::store('redis')->forget($this->progressKey($session->id));
        } catch (\Throwable $e) {
            report($e);
            Cache::store()->forget($this->progressKey($session->id));
        }
    }

    public function leaderboardKey(int $contestId): string
    {
        return "typing:leaderboard:contest:{$contestId}";
    }

    private function progressKey(int $sessionId): string
    {
        return "typing:session:{$sessionId}:progress";
    }
}
