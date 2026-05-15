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
    public array $channels;
    public int $userId;
    public string $title;
    public string $message;
    public array $payload;

    public function __construct(
        array $channels,
        int $userId,
        string $title,
        string $message,
        array $payload = [],
    ) {
        $this->channels = $channels;
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->payload = $payload;

        $this->onQueue('notifications');
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
