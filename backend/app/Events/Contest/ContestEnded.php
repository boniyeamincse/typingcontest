<?php

namespace App\Events\Contest;

use App\Models\Contest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContestEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Contest $contest) {}

    public function broadcastOn(): array
    {
        return [new Channel("contest.{$this->contest->id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'contest_id' => $this->contest->id,
            'ended_at'   => $this->contest->ended_at?->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contest.ended';
    }
}
