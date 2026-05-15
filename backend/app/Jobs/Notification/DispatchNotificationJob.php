<?php

namespace App\Jobs\Notification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'notifications';

    public function __construct(
        private readonly array $channels,
        private readonly int $userId,
        private readonly string $title,
        private readonly string $message,
        private readonly array $payload = [],
    ) {
    }

    public function handle(): void
    {
        // Placeholder delivery fanout for providers (mail, push, websocket).
        Log::info('Notification dispatch queued', [
            'channels' => $this->channels,
            'user_id' => $this->userId,
            'title' => $this->title,
            'message' => $this->message,
            'payload' => $this->payload,
        ]);
    }
}
