<?php

namespace App\Services\Typing;

use App\Events\Typing\TypingError;
use App\Events\Typing\TypingFinished;
use App\Events\Typing\TypingProgress;
use App\Events\Typing\TypingStarted;
use App\Events\Typing\TypingUpdate;
use App\Jobs\Typing\AutoSubmitTypingSession;
use App\Models\Contest;
use App\Models\TypingSession;
use App\Models\User;
use App\Repositories\Typing\TypingInputRepositoryInterface;
use App\Repositories\Typing\TypingResultRepositoryInterface;
use App\Repositories\Typing\TypingSessionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TypingEngineService
{
    public function __construct(
        private readonly TypingSessionRepositoryInterface $sessionRepo,
        private readonly TypingInputRepositoryInterface $inputRepo,
        private readonly TypingResultRepositoryInterface $resultRepo,
        private readonly TypingMetricService $metricService,
        private readonly TypingAntiCheatService $antiCheatService,
        private readonly TypingRealtimeService $realtimeService,
    ) {}

    public function startSession(User $user, array $payload): TypingSession
    {
        $contest = Contest::with('rule')->find($payload['contest_id']);

        if (!$contest) {
            throw new ModelNotFoundException('Contest not found.');
        }

        if ($contest->status !== Contest::STATUS_ACTIVE) {
            throw new HttpException(422, 'Session starts only when contest is active');
        }

        $existing = $this->sessionRepo->findUserSessionInContest($contest->id, $user->id);
        if ($existing) {
            throw new HttpException(409, 'Session already exists');
        }

        $durationSeconds = (int) (
            $contest->duration_seconds
            ?? (($contest->duration_minutes ?? $contest->rule?->duration_minutes ?? 1) * 60)
        );

        $session = DB::transaction(function () use ($user, $contest, $payload, $durationSeconds) {
            return $this->sessionRepo->createForUser($user, [
                'contest_id' => $contest->id,
                'session_uuid' => (string) Str::uuid(),
                'status' => TypingSession::STATUS_COUNTDOWN,
                'duration_seconds' => max(1, $durationSeconds),
                'countdown_started_at' => now(),
                'started_at' => now()->addSeconds(3),
                'expires_at' => now()->addSeconds(3 + max(1, $durationSeconds)),
                'countdown_seconds' => 3,
                'ip_address' => $payload['ip_address'] ?? null,
                'user_agent' => $payload['user_agent'] ?? null,
                'device_fingerprint' => $payload['device_fingerprint'] ?? null,
            ]);
        });

        if (config('queue.default') !== 'sync') {
            AutoSubmitTypingSession::dispatch($session->id)->delay($session->expires_at);
        }

        $this->safeBroadcast(new TypingStarted($session));

        return $session;
    }

    public function updateSession(User $user, int $sessionId, array $payload): array
    {
        $session = $this->sessionRepo->findById($sessionId);

        if (!$session || $session->user_id !== $user->id) {
            throw new HttpException(404, 'Typing session not found');
        }

        if ($session->isFinalized()) {
            throw new HttpException(422, 'Session already finalized');
        }

        if (now()->greaterThan($session->expires_at)) {
            $this->submitSession($user, $sessionId, [
                'typed_text' => (string) ($payload['typed_text'] ?? ''),
                'elapsed_ms' => (int) ($payload['elapsed_ms'] ?? ($session->duration_seconds * 1000)),
                'auto_submit' => true,
            ]);

            throw new HttpException(422, 'Session expired and auto-submitted');
        }

        if ($session->status === TypingSession::STATUS_COUNTDOWN && now()->greaterThanOrEqualTo($session->started_at)) {
            $session = $this->sessionRepo->updateSession($session, ['status' => TypingSession::STATUS_ACTIVE]);
        }

        $expectedText = (string) $session->contest->getTypingContent();
        $typedText = (string) ($payload['typed_text'] ?? '');
        $elapsedMs = (int) ($payload['elapsed_ms'] ?? 0);

        $metrics = $this->metricService->analyze($expectedText, $typedText, max(1, $elapsedMs));

        $antiCheat = $this->antiCheatService->inspectUpdate($session, $payload, $metrics);
        if ($antiCheat['flagged']) {
            $this->antiCheatService->persistFlags($session, $antiCheat['flags'], $payload['ip_address'] ?? null);
        }

        $sequence = (int) ($payload['sequence'] ?? 0);
        if ($sequence > 0) {
            $this->inputRepo->storeInput($session, array_merge($payload, $metrics));
            $this->inputRepo->storeProgress($session, array_merge($payload, $metrics));
        }

        if (!empty($payload['error_event'])) {
            $this->inputRepo->storeError($session, [
                'sequence' => $sequence,
                'word_index' => (int) ($payload['word_index'] ?? 0),
                'char_index' => (int) ($payload['char_index'] ?? 0),
                'expected_char' => $payload['expected_char'] ?? null,
                'typed_char' => $payload['typed_char'] ?? null,
                'error_type' => $payload['error_type'] ?? 'mismatch',
            ]);

            $this->safeBroadcast(new TypingError($session, [
                'sequence' => $sequence,
                'error_type' => $payload['error_type'] ?? 'mismatch',
            ]));
        }

        $this->sessionRepo->updateSession($session, ['last_sequence' => max($session->last_sequence, $sequence)]);

        $this->realtimeService->storePartialProgress($session, array_merge($metrics, [
            'sequence' => $sequence,
            'typed_text' => $typedText,
        ]));

        $this->safeBroadcast(new TypingUpdate($session, $metrics));
        $this->safeBroadcast(new TypingProgress($session, $metrics));

        return [
            'session' => $session->fresh(),
            'metrics' => $metrics,
            'anti_cheat' => $antiCheat,
        ];
    }

    public function submitSession(User $user, int $sessionId, array $payload): array
    {
        $session = $this->sessionRepo->findById($sessionId);

        if (!$session || $session->user_id !== $user->id) {
            throw new HttpException(404, 'Typing session not found');
        }

        if ($session->status === TypingSession::STATUS_SUBMITTED) {
            throw new HttpException(409, 'Session already submitted');
        }

        $expectedText = (string) $session->contest->getTypingContent();
        $typedText = (string) ($payload['typed_text'] ?? '');
        $elapsedMs = (int) ($payload['elapsed_ms'] ?? ($session->duration_seconds * 1000));

        $metrics = $this->metricService->analyze($expectedText, $typedText, max(1, $elapsedMs));

        $isAuto = (bool) ($payload['auto_submit'] ?? false) || now()->greaterThan($session->expires_at);

        $antiCheat = $this->antiCheatService->inspectUpdate($session, $payload, $metrics);
        if ($antiCheat['flagged']) {
            $this->antiCheatService->persistFlags($session, $antiCheat['flags'], $payload['ip_address'] ?? null);
        }

        $status = $antiCheat['flagged'] ? TypingSession::STATUS_DISQUALIFIED : TypingSession::STATUS_SUBMITTED;

        $updatedSession = DB::transaction(function () use ($session, $status, $isAuto, $antiCheat, $metrics) {
            $updatedSession = $this->sessionRepo->updateSession($session, [
                'status' => $status,
                'submitted_at' => now(),
                'auto_submitted' => $isAuto,
                'is_flagged' => $antiCheat['flagged'],
                'disqualified_reason' => $antiCheat['flagged'] ? implode(', ', $antiCheat['flags']) : null,
            ]);

            $result = $this->resultRepo->upsertForSession($updatedSession, [
                'correct_words' => $metrics['correct_words'],
                'correct_characters' => $metrics['correct_characters'],
                'total_characters' => $metrics['total_characters'],
                'errors' => $metrics['errors'],
                'cpm' => $metrics['cpm'],
                'wpm' => $metrics['wpm'],
                'accuracy' => $metrics['accuracy'],
                'score' => $metrics['score'],
                'progress_percent' => $metrics['progress_percent'],
                'duration_seconds' => $metrics['duration_seconds'],
                'is_disqualified' => $antiCheat['flagged'],
                'disqualified_reason' => $antiCheat['flagged'] ? implode(', ', $antiCheat['flags']) : null,
                'meta' => ['auto_submit' => $isAuto, 'flags' => $antiCheat['flags']],
            ]);

            return [$updatedSession, $result];
        });

        [$submittedSession, $result] = $updatedSession;

        $this->realtimeService->clearPartialProgress($submittedSession);

        $this->safeBroadcast(new TypingFinished($submittedSession, $result));

        return [
            'session' => $submittedSession,
            'result' => $result,
            'metrics' => $metrics,
            'anti_cheat' => $antiCheat,
        ];
    }

    public function getSessionStatus(User $user, int $sessionId): array
    {
        $session = $this->sessionRepo->findById($sessionId);

        if (!$session || $session->user_id !== $user->id) {
            throw new HttpException(404, 'Typing session not found');
        }

        return [
            'session' => $session,
            'live' => $this->realtimeService->getPartialProgress($session),
        ];
    }

    private function safeBroadcast(object $event): void
    {
        if (config('broadcasting.default') === 'redis' && !class_exists(\Redis::class)) {
            return;
        }

        try {
            event($event);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
