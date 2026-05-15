<?php

namespace App\Events\Contest;

use App\Models\Contest;
use App\Models\Result;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Contest $contest,
        public readonly User    $user,
        public readonly Result  $result,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("contest.{$this->contest->id}"),
            new Channel("user.{$this->user->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'contest_id' => $this->contest->id,
            'user_id'    => $this->user->id,
            'username'   => $this->user->username,
            'wpm'        => $this->result->wpm,
            'accuracy'   => $this->result->accuracy,
            'errors'     => $this->result->errors,
            'score'      => $this->result->score,
            'rank'       => $this->result->rank,
        ];
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }
}
