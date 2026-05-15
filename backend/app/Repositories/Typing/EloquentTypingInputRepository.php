<?php

namespace App\Repositories\Typing;

use App\Models\TypingError;
use App\Models\TypingInput;
use App\Models\TypingProgressLog;
use App\Models\TypingSession;

class EloquentTypingInputRepository implements TypingInputRepositoryInterface
{
    public function storeInput(TypingSession $session, array $payload): void
    {
        TypingInput::updateOrCreate(
            [
                'typing_session_id' => $session->id,
                'sequence' => (int) $payload['sequence'],
            ],
            [
                'cursor_position' => (int) ($payload['cursor_position'] ?? 0),
                'correct_characters' => (int) ($payload['correct_characters'] ?? 0),
                'total_characters' => (int) ($payload['total_characters'] ?? 0),
                'errors' => (int) ($payload['errors'] ?? 0),
                'progress_percent' => (int) ($payload['progress_percent'] ?? 0),
                'payload' => $payload['payload'] ?? null,
                'captured_at' => now(),
            ]
        );
    }

    public function storeProgress(TypingSession $session, array $payload): void
    {
        TypingProgressLog::create([
            'typing_session_id' => $session->id,
            'sequence' => (int) ($payload['sequence'] ?? 0),
            'elapsed_ms' => (int) ($payload['elapsed_ms'] ?? 0),
            'wpm' => (int) ($payload['wpm'] ?? 0),
            'cpm' => (int) ($payload['cpm'] ?? 0),
            'accuracy' => round((float) ($payload['accuracy'] ?? 0), 2),
            'errors' => (int) ($payload['errors'] ?? 0),
            'progress_percent' => (int) ($payload['progress_percent'] ?? 0),
            'snapshot' => $payload['snapshot'] ?? null,
            'logged_at' => now(),
        ]);
    }

    public function storeError(TypingSession $session, array $payload): void
    {
        TypingError::create([
            'typing_session_id' => $session->id,
            'sequence' => (int) ($payload['sequence'] ?? 0),
            'word_index' => (int) ($payload['word_index'] ?? 0),
            'char_index' => (int) ($payload['char_index'] ?? 0),
            'expected_char' => $payload['expected_char'] ?? null,
            'typed_char' => $payload['typed_char'] ?? null,
            'error_type' => $payload['error_type'] ?? 'mismatch',
            'detected_at' => now(),
        ]);
    }
}
