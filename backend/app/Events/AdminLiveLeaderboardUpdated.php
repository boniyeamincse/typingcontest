<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminLiveLeaderboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $contestId, public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.live.leaderboard.'.$this->contestId)];
    }

    public function broadcastAs(): string
    {
        return 'admin.live.leaderboard.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
