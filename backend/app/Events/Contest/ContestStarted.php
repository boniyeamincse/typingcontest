<?php

namespace App\Events\Contest;

use App\Models\Contest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContestStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Contest $contest) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("contest.{$this->contest->id}"),
            new PresenceChannel("contest.{$this->contest->id}.lobby"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'contest_id' => $this->contest->id,
            'title'      => $this->contest->title,
            'started_at' => $this->contest->started_at?->toISOString(),
            'duration'   => $this->contest->duration_minutes ?? $this->contest->rule?->duration_minutes,
        ];
    }

    public function broadcastAs(): string
    {
        return 'contest.started';
    }
}
