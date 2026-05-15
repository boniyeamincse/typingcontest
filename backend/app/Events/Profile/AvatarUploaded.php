<?php

namespace App\Events\Profile;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvatarUploaded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $avatarUrl,
        public readonly ?string $ip = null,
    ) {}
}
