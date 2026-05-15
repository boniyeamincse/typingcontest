<?php

use App\Models\Contest;
use App\Models\TypingSession;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('typing.user.{userId}', function ($user, int $userId): bool {
    return (int) $user->id === $userId;
});

Broadcast::channel('typing.session.{sessionId}', function ($user, int $sessionId): bool {
    return TypingSession::where('id', $sessionId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('typing.leaderboard.{contestId}', function ($user, int $contestId): bool {
    $contest = Contest::find($contestId);

    if (!$contest) {
        return false;
    }

    if ($user->hasRole('admin')) {
        return true;
    }

    return TypingSession::where('contest_id', $contestId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('leaderboard.global', function ($user): bool {
    return (bool) $user;
});

Broadcast::channel('leaderboard.daily', function ($user): bool {
    return (bool) $user;
});

Broadcast::channel('leaderboard.weekly', function ($user): bool {
    return (bool) $user;
});

Broadcast::channel('leaderboard.monthly', function ($user): bool {
    return (bool) $user;
});

Broadcast::channel('leaderboard.contest.{contestId}', function ($user, int $contestId): bool {
    return Contest::where('id', $contestId)->exists();
});

Broadcast::channel('leaderboard.country.{countryCode}', function ($user, string $countryCode): bool {
    return strlen($countryCode) === 2;
});

Broadcast::channel('subscription.user.{userId}', function ($user, int $userId): bool {
    return (int) $user->id === $userId;
});

Broadcast::channel('admin.notifications', function ($user): bool {
    return $user->hasAnyRole(['super_admin', 'contest_admin', 'user_moderator', 'support_admin', 'content_manager', 'admin']);
});

Broadcast::channel('admin.live.contest.{contestId}', function ($user, int $contestId): bool {
    return $user->hasAnyRole(['super_admin', 'contest_admin', 'admin']);
});

Broadcast::channel('admin.live.leaderboard.{contestId}', function ($user, int $contestId): bool {
    return $user->hasAnyRole(['super_admin', 'contest_admin', 'admin']);
});

Broadcast::channel('admin.security.cheating', function ($user): bool {
    return $user->hasAnyRole(['super_admin', 'user_moderator', 'admin']);
});
