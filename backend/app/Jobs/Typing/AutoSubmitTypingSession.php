<?php

namespace App\Jobs\Typing;

use App\Models\TypingSession;
use App\Services\Typing\TypingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoSubmitTypingSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(private readonly int $sessionId) {}

    public function handle(TypingEngineService $typingEngineService): void
    {
        $session = TypingSession::find($this->sessionId);

        if (!$session || $session->isFinalized()) {
            return;
        }

        try {
            $typingEngineService->submitSession($session->user, $session->id, [
                'typed_text' => '',
                'elapsed_ms' => $session->duration_seconds * 1000,
                'auto_submit' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error("AutoSubmitTypingSession failed for session #{$this->sessionId}: {$e->getMessage()}");
        }
    }
}
