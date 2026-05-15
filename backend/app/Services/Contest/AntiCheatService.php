<?php

namespace App\Services\Contest;

use App\Models\AntiCheatLog;
use App\Models\Contest;
use App\Models\ContestSession;
use App\Models\User;
use App\Repositories\Contest\ParticipantRepositoryInterface;

class AntiCheatService
{
    public function __construct(
        private readonly ParticipantRepositoryInterface $participantRepo,
    ) {}

    /**
     * Inspect a session just before or at submission and log any violations.
     *
     * @return array{passed: bool, flags: string[]}
     */
    public function inspect(Contest $contest, ContestSession $session): array
    {
        $rule  = $contest->rule;
        $flags = [];

        if (!$rule || !$rule->anti_cheat_enabled) {
            return ['passed' => true, 'flags' => []];
        }

        // Check tab switches
        if ($rule->max_tab_switches !== null && $session->tab_switches > $rule->max_tab_switches) {
            $flags[] = AntiCheatLog::EVENT_TAB_SWITCH;
            $this->log($contest, $session->user_id, AntiCheatLog::EVENT_TAB_SWITCH, AntiCheatLog::SEVERITY_MEDIUM, [
                'switches' => $session->tab_switches,
                'max'      => $rule->max_tab_switches,
            ], $session->ip_address);
        }

        // Check paste
        if (!$rule->allow_paste && $session->paste_attempts > 0) {
            $flags[] = AntiCheatLog::EVENT_PASTE_ATTEMPT;
            $this->log($contest, $session->user_id, AntiCheatLog::EVENT_PASTE_ATTEMPT, AntiCheatLog::SEVERITY_HIGH, [
                'attempts' => $session->paste_attempts,
            ], $session->ip_address);
        }

        return [
            'passed' => empty($flags),
            'flags'  => $flags,
        ];
    }

    /**
     * Check for suspiciously high WPM after score is computed.
     */
    public function checkWpm(Contest $contest, User $user, int $wpm, ?string $ipAddress = null): bool
    {
        $rule = $contest->rule;

        if (!$rule || !$rule->anti_cheat_enabled) {
            return false;
        }

        $threshold = $rule->max_wpm_threshold ?? 250;

        if ($wpm > $threshold) {
            $this->log($contest, $user->id, AntiCheatLog::EVENT_SUSPICIOUS_WPM, AntiCheatLog::SEVERITY_HIGH, [
                'wpm'       => $wpm,
                'threshold' => $threshold,
            ], $ipAddress);

            return true;
        }

        return false;
    }

    /**
     * Record a single anti-cheat event.
     */
    public function log(
        Contest $contest,
        int $userId,
        string $eventType,
        string $severity = AntiCheatLog::SEVERITY_LOW,
        array $metadata = [],
        ?string $ipAddress = null,
        bool $autoFlagged = true
    ): AntiCheatLog {
        return AntiCheatLog::create([
            'contest_id'   => $contest->id,
            'user_id'      => $userId,
            'event_type'   => $eventType,
            'ip_address'   => $ipAddress,
            'severity'     => $severity,
            'auto_flagged' => $autoFlagged,
            'metadata'     => $metadata,
            'logged_at'    => now(),
        ]);
    }

    /**
     * Record a tab-switch event from a live WebSocket/HTTP ping.
     */
    public function recordTabSwitch(ContestSession $session): void
    {
        $session->increment('tab_switches');

        $this->log(
            $session->contest,
            $session->user_id,
            AntiCheatLog::EVENT_TAB_SWITCH,
            AntiCheatLog::SEVERITY_LOW,
            ['count' => $session->tab_switches + 1],
            $session->ip_address
        );
    }

    /**
     * Record a paste attempt.
     */
    public function recordPasteAttempt(ContestSession $session): void
    {
        $session->increment('paste_attempts');

        $this->log(
            $session->contest,
            $session->user_id,
            AntiCheatLog::EVENT_PASTE_ATTEMPT,
            AntiCheatLog::SEVERITY_MEDIUM,
            ['count' => $session->paste_attempts + 1],
            $session->ip_address
        );
    }
}
