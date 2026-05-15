<?php

namespace App\Events\Leaderboard;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaderboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $type,
        public readonly string $periodKey,
        public readonly array $top,
        public readonly ?int $contestId = null,
        public readonly ?string $countryCode = null,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->type === 'contest' && $this->contestId) {
            $channels[] = new Channel("leaderboard.contest.{$this->contestId}");
        }

        if ($this->type === 'global') {
            $channels[] = new Channel('leaderboard.global');
        }

        if ($this->type === 'daily') {
            $channels[] = new Channel('leaderboard.daily');
        }

        if ($this->type === 'weekly') {
            $channels[] = new Channel('leaderboard.weekly');
        }

        if ($this->type === 'monthly') {
            $channels[] = new Channel('leaderboard.monthly');
        }

        if ($this->type === 'country' && $this->countryCode) {
            $channels[] = new Channel('leaderboard.country.' . strtoupper($this->countryCode));
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'leaderboard.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'period_key' => $this->periodKey,
            'contest_id' => $this->contestId,
            'country_code' => $this->countryCode,
            'top_10' => $this->top,
            'updated_at' => now()->toISOString(),
        ];
    }
}
