<?php

namespace App\Repositories\Typing;

use App\Models\TypingSession;

interface TypingInputRepositoryInterface
{
    public function storeInput(TypingSession $session, array $payload): void;

    public function storeProgress(TypingSession $session, array $payload): void;

    public function storeError(TypingSession $session, array $payload): void;
}
