<?php

namespace App\Services\Contest;

use App\Events\Contest\ContestEnded;
use App\Events\Contest\ContestStarted;
use App\Events\Contest\ParticipantJoined;
use App\Events\Contest\RankUpdated;
use App\Events\Contest\ScoreUpdated;
use App\Exceptions\Contest\ContestException;
use App\Models\Contest;
use App\Models\ContestRule;
use App\Models\ContestSession;
use App\Models\Result;
use App\Models\User;
use App\Repositories\Contest\ContestRepositoryInterface;
use App\Repositories\Contest\ParticipantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ContestService
{
    public function __construct(
        private readonly ContestRepositoryInterface    $contestRepo,
        private readonly ParticipantRepositoryInterface $participantRepo,
        private readonly ScoreService                  $scoreService,
        private readonly AntiCheatService              $antiCheatService,
        private readonly LeaderboardService            $leaderboardService,
    ) {}

    // ── Listing ───────────────────────────────────────────────────────────

    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->contestRepo->paginate($filters, $perPage);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────

    public function create(array $data, int $creatorId): Contest
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $data['created_by'] = $creatorId;
            $data['status']     = Contest::STATUS_DRAFT;

            $contest = $this->contestRepo->create($data);

            if (!empty($data['rules'])) {
                ContestRule::create(array_merge($data['rules'], ['contest_id' => $contest->id]));
            }

            return $contest->load('rule');
        });
    }

    public function update(Contest $contest, array $data): Contest
    {
        return DB::transaction(function () use ($contest, $data) {
            if (isset($data['rules'])) {
                $contest->rule
                    ? $contest->rule->update($data['rules'])
                    : ContestRule::create(array_merge($data['rules'], ['contest_id' => $contest->id]));

                unset($data['rules']);
            }

            return $this->contestRepo->update($contest, $data);
        });
    }

    public function delete(Contest $contest): void
    {
        if ($contest->status === Contest::STATUS_ACTIVE) {
            throw ContestException::cannotDeleteActive();
        }

        $this->contestRepo->delete($contest);
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────

    public function publish(Contest $contest): Contest
    {
        if ($contest->status !== Contest::STATUS_DRAFT) {
            throw ContestException::invalidTransition($contest->status, Contest::STATUS_PUBLISHED);
        }

        $contest->update(['status' => Contest::STATUS_PUBLISHED]);

        return $contest->fresh();
    }

    public function start(Contest $contest): Contest
    {
        if (!in_array($contest->status, [Contest::STATUS_PUBLISHED, Contest::STATUS_DRAFT])) {
            throw ContestException::invalidTransition($contest->status, Contest::STATUS_ACTIVE);
        }

        $contest = $this->contestRepo->activate($contest);

        try {
            broadcast(new ContestStarted($contest))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return $contest;
    }

    public function end(Contest $contest): Contest
    {
        if ($contest->status !== Contest::STATUS_ACTIVE) {
            throw ContestException::invalidTransition($contest->status, Contest::STATUS_FINISHED);
        }

        $contest = $this->contestRepo->finish($contest);

        $this->participantRepo->recalculateRanks($contest);
        $this->leaderboardService->invalidateContestCache($contest);

        try {
            broadcast(new ContestEnded($contest))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return $contest;
    }

    public function pause(Contest $contest): Contest
    {
        if ($contest->status !== Contest::STATUS_ACTIVE) {
            throw ContestException::invalidTransition($contest->status, 'paused');
        }

        return $this->contestRepo->setPaused($contest, true);
    }

    public function resume(Contest $contest): Contest
    {
        if (!$contest->is_paused) {
            throw ContestException::notPaused();
        }

        return $this->contestRepo->setPaused($contest, false);
    }

    public function cancel(Contest $contest): Contest
    {
        if ($contest->status === Contest::STATUS_FINISHED) {
            throw ContestException::alreadyFinished();
        }

        return $this->contestRepo->cancel($contest);
    }

    // ── Joining ───────────────────────────────────────────────────────────

    public function join(Contest $contest, User $user, array $meta = []): array
    {
        if (!$contest->isJoinable()) {
            throw ContestException::notJoinable();
        }

        $existing = $this->participantRepo->findParticipant($contest, $user);
        if ($existing) {
            throw ContestException::alreadyJoined();
        }

        return DB::transaction(function () use ($contest, $user, $meta) {
            $participant = $this->participantRepo->join($contest, $user);
            $session     = $this->participantRepo->createSession($contest, $user, $meta);

            try {
                broadcast(new ParticipantJoined($contest, $user))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }

            return compact('participant', 'session');
        });
    }

    // ── Submission ────────────────────────────────────────────────────────

    public function submitResult(Contest $contest, User $user, array $input): Result
    {
        $participant = $this->participantRepo->findParticipant($contest, $user);

        if (!$participant) {
            throw ContestException::notJoined();
        }

        if ($participant->is_disqualified) {
            throw ContestException::disqualified();
        }

        if ($participant->submitted_at) {
            throw ContestException::alreadySubmitted();
        }

        $session = $this->participantRepo->findSession($contest, $user);

        // Anti-cheat inspection
        if ($session) {
            $antiCheatResult = $this->antiCheatService->inspect($contest, $session);
        }

        // Calculate metrics (supports both legacy and new payloads)
        if (isset($input['wpm'], $input['accuracy'])) {
            $metrics = [
                'wpm'      => (int) $input['wpm'],
                'accuracy' => round((float) $input['accuracy'], 2),
                'errors'   => (int) $input['errors'],
                'score'    => $this->scoreService->calculateScore((int) $input['wpm'], (float) $input['accuracy'], (int) $input['errors']),
            ];
        } else {
            $metrics = $this->scoreService->compute([
                'characters_typed' => (int) ($input['characters_typed'] ?? 0),
                'errors'           => (int) ($input['errors'] ?? 0),
                'elapsed_seconds'  => (float) ($input['elapsed_seconds'] ?? 0),
            ]);
        }

        // Check suspicious WPM
        $isSuspicious = $this->antiCheatService->checkWpm(
            $contest,
            $user,
            $metrics['wpm'],
            $session?->ip_address
        );

        return DB::transaction(function () use ($contest, $user, $participant, $session, $metrics, $isSuspicious) {
            $participant->update([
                'wpm'          => $metrics['wpm'],
                'accuracy'     => $metrics['accuracy'],
                'errors'       => $metrics['errors'],
                'submitted_at' => now(),
            ]);

            if ($session) {
                $this->participantRepo->updateSession($session, [
                    'status'       => ContestSession::STATUS_SUBMITTED,
                    'submitted_at' => now(),
                ]);
            }

            // Recalculate ranks for this contest
            $this->participantRepo->recalculateRanks($contest);

            // Invalidate caches
            $this->leaderboardService->invalidateContestCache($contest);

            $participant->refresh();

            try {
                broadcast(new ScoreUpdated($contest, $user, $participant))->toOthers();
                broadcast(new RankUpdated($contest, $participant->rank))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }

            return $participant;
        });
    }
}
