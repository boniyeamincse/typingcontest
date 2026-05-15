<?php

namespace App\Repositories\Leaderboard;

use App\Models\ContestRanking;
use App\Models\TypingResult;
use App\Models\UserScore;
use Illuminate\Support\Collection;

interface LeaderboardRepositoryInterface
{
    public function upsertUserScore(TypingResult $result, array $scoreBreakdown, ?string $anomalyFlag = null): UserScore;

    public function upsertContestRanking(TypingResult $result, UserScore $userScore): ContestRanking;

    public function recalculateContestRanks(int $contestId): Collection;

    public function fetchContestLeaderboard(int $contestId, int $limit = 10): Collection;

    public function aggregateScope(string $type, ?string $periodKey = null, ?string $countryCode = null): Collection;

    public function upsertRankingRow(int $userId, string $type, ?string $periodKey, ?string $countryCode, array $data): void;

    public function upsertLeaderboardRow(int $userId, string $type, ?string $periodKey, ?string $countryCode, array $data): void;

    public function fetchLeaderboard(string $type, ?string $periodKey = null, ?string $countryCode = null, int $limit = 10): Collection;

    public function insertRankingHistory(array $rows): void;
}
