<?php

namespace App\Events\Typing;

use App\Models\TypingResult;
use App\Models\TypingSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingFinished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly TypingSession $session,
        public readonly TypingResult $result,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("typing.session.{$this->session->id}"),
            new Channel("typing.leaderboard.{$this->session->contest_id}"),
            new Channel("typing.user.{$this->session->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing.finished';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'contest_id' => $this->session->contest_id,
            'result' => [
                'wpm' => $this->result->wpm,
                'cpm' => $this->result->cpm,
                'accuracy' => $this->result->accuracy,
                'errors' => $this->result->errors,
                'score' => $this->result->score,
            ],
        ];
    }
}
