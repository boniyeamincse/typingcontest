<?php

namespace App\Repositories\Typing;

use App\Models\TypingResult;
use App\Models\TypingSession;

interface TypingResultRepositoryInterface
{
    public function upsertForSession(TypingSession $session, array $data): TypingResult;

    public function findById(int $id): ?TypingResult;
}
