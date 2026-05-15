<?php

namespace App\Events\Social;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $senderId,
        public readonly int $receiverId,
    ) {
    }
}
