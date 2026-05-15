<?php

namespace App\Events\Contest;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Contest $contest,
        public readonly User    $user,
    ) {}

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
            'contest_id'       => $this->contest->id,
            'user_id'          => $this->user->id,
            'username'         => $this->user->username,
            'avatar'           => $this->user->avatar,
            'participant_count' => $this->contest->participants()->count(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'participant.joined';
    }
}
