<?php

namespace App\Listeners\Profile;

use App\Events\Profile\ProfileUpdated;
use App\Repositories\Profile\ActivityRepositoryInterface;
use App\Models\UserActivity;

class LogProfileActivity
{
    public function __construct(
        private readonly ActivityRepositoryInterface $activityRepo,
    ) {}

    public function handle(ProfileUpdated $event): void
    {
        $this->activityRepo->log(
            $event->user,
            UserActivity::TYPE_PROFILE_UPDATE,
            'Profile updated: ' . implode(', ', $event->changedFields),
            ['fields' => $event->changedFields],
            $event->ip
        );
    }
}
