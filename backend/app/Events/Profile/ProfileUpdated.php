<?php

namespace App\Events\Profile;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly array  $changedFields,
        public readonly ?string $ip = null,
    ) {}
}
