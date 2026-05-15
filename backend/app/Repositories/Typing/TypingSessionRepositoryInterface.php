<?php

namespace App\Repositories\Typing;

use App\Models\TypingSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TypingSessionRepositoryInterface
{
    public function findById(int $id): ?TypingSession;

    public function findBySessionUuid(string $sessionUuid): ?TypingSession;

    public function findUserSessionInContest(int $contestId, int $userId): ?TypingSession;

    public function createForUser(User $user, array $data): TypingSession;

    public function updateSession(TypingSession $session, array $data): TypingSession;

    public function paginateUserHistory(int $userId, int $perPage = 15): LengthAwarePaginator;
}
