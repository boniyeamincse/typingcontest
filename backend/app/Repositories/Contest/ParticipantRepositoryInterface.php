<?php

namespace App\Repositories\Contest;

use App\Models\Contest;
use App\Models\ContestSession;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ParticipantRepositoryInterface
{
    public function findParticipant(Contest $contest, User $user): ?Result;

    public function join(Contest $contest, User $user): Result;

    public function getLeaderboard(Contest $contest, int $limit = 50): Collection;

    public function recalculateRanks(Contest $contest): void;

    public function disqualify(Result $result, string $reason): void;

    // Session methods
    public function findSession(Contest $contest, User $user): ?ContestSession;

    public function createSession(Contest $contest, User $user, array $meta): ContestSession;

    public function updateSession(ContestSession $session, array $data): ContestSession;
}
