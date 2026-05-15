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
