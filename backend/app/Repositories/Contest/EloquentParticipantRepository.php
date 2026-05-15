<?php

namespace App\Repositories\Contest;

use App\Models\Contest;
use App\Models\ContestSession;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class EloquentParticipantRepository implements ParticipantRepositoryInterface
{
    public function findParticipant(Contest $contest, User $user): ?Result
    {
        return Result::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function join(Contest $contest, User $user): Result
    {
        return Result::firstOrCreate(
            ['contest_id' => $contest->id, 'user_id' => $user->id],
            ['joined_at' => now(), 'is_disqualified' => false]
        );
    }

    public function getLeaderboard(Contest $contest, int $limit = 50): Collection
    {
        return Result::where('contest_id', $contest->id)
            ->where('is_disqualified', false)
            ->whereNotNull('submitted_at')
            ->orderBy('rank', 'asc')
            ->with('user:id,username,avatar,country')
            ->limit($limit)
            ->get();
    }

    public function recalculateRanks(Contest $contest): void
    {
        $results = Result::where('contest_id', $contest->id)
            ->where('is_disqualified', false)
            ->whereNotNull('submitted_at')
            ->orderByDesc('score')
            ->orderByDesc('wpm')
            ->get();

        foreach ($results as $index => $result) {
            $result->update(['rank' => $index + 1]);
        }
    }

    public function disqualify(Result $result, string $reason): void
    {
        $result->update([
            'is_disqualified'     => true,
            'disqualified_reason' => $reason,
        ]);
    }

    // ── Session helpers ────────────────────────────────────────────────────

    public function findSession(Contest $contest, User $user): ?ContestSession
    {
        return ContestSession::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    public function createSession(Contest $contest, User $user, array $meta): ContestSession
    {
        return ContestSession::create([
            'contest_id'       => $contest->id,
            'user_id'          => $user->id,
            'session_token'    => Str::uuid()->toString(),
            'status'           => ContestSession::STATUS_WAITING,
            'joined_at'        => now(),
            'ip_address'       => $meta['ip_address'] ?? null,
            'user_agent'       => $meta['user_agent'] ?? null,
            'device_fingerprint' => $meta['device_fingerprint'] ?? null,
            'tab_switches'     => 0,
            'paste_attempts'   => 0,
            'is_flagged'       => false,
        ]);
    }

    public function updateSession(ContestSession $session, array $data): ContestSession
    {
        $session->fill($data)->save();

        return $session->fresh();
    }
}
