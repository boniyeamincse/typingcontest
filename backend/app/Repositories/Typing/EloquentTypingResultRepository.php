<?php

namespace App\Repositories\Typing;

use App\Models\TypingResult;
use App\Models\TypingSession;

class EloquentTypingResultRepository implements TypingResultRepositoryInterface
{
    public function upsertForSession(TypingSession $session, array $data): TypingResult
    {
        return TypingResult::updateOrCreate(
            ['typing_session_id' => $session->id],
            array_merge($data, [
                'contest_id' => $session->contest_id,
                'user_id' => $session->user_id,
            ])
        );
    }

    public function findById(int $id): ?TypingResult
    {
        return TypingResult::with(['session.contest', 'user'])->find($id);
    }
}
