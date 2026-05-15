<?php

namespace App\Events\Typing;

use App\Models\TypingSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly TypingSession $session,
        public readonly array $payload,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("typing.session.{$this->session->id}"),
            new Channel("typing.leaderboard.{$this->session->contest_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'contest_id' => $this->session->contest_id,
            'progress' => $this->payload,
        ];
    }
}
