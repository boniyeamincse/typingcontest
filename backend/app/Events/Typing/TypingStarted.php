<?php

namespace App\Events\Typing;

use App\Models\TypingSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly TypingSession $session) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("typing.session.{$this->session->id}"),
            new Channel("typing.user.{$this->session->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing.started';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'status' => $this->session->status,
            'countdown_seconds' => $this->session->countdown_seconds,
            'started_at' => $this->session->started_at?->toISOString(),
            'expires_at' => $this->session->expires_at?->toISOString(),
        ];
    }
}
