<?php

namespace App\Repositories\Typing;

use App\Models\TypingSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTypingSessionRepository implements TypingSessionRepositoryInterface
{
    public function findById(int $id): ?TypingSession
    {
        return TypingSession::with(['contest', 'result', 'user'])->find($id);
    }

    public function findBySessionUuid(string $sessionUuid): ?TypingSession
    {
        return TypingSession::where('session_uuid', $sessionUuid)
            ->with(['contest', 'result', 'user'])
            ->first();
    }

    public function findUserSessionInContest(int $contestId, int $userId): ?TypingSession
    {
        return TypingSession::where('contest_id', $contestId)
            ->where('user_id', $userId)
            ->first();
    }

    public function createForUser(User $user, array $data): TypingSession
    {
        $data['user_id'] = $user->id;

        return TypingSession::create($data);
    }

    public function updateSession(TypingSession $session, array $data): TypingSession
    {
        $session->fill($data)->save();

        return $session->fresh(['contest', 'result', 'user']);
    }

    public function paginateUserHistory(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return TypingSession::where('user_id', $userId)
            ->with(['contest:id,title,type,status', 'result'])
            ->latest('id')
            ->paginate($perPage);
    }
}
