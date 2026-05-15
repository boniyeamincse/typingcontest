<?php

namespace App\Events\Social;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFollowed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $followerId,
        public readonly int $followedId,
    ) {
    }
}
