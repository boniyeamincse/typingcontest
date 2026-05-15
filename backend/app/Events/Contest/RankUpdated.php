<?php

namespace App\Events\Contest;

use App\Models\Contest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RankUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Contest $contest,
        public readonly int     $currentRank,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("contest.{$this->contest->id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'contest_id'   => $this->contest->id,
            'current_rank' => $this->currentRank,
        ];
    }

    public function broadcastAs(): string
    {
        return 'rank.updated';
    }
}
