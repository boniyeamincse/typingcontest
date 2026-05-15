<?php

namespace App\Services\Typing;

use App\Models\AntiCheatLog;
use App\Models\TypingSession;

class TypingAntiCheatService
{
    private const MAX_WPM = 320;
    private const MAX_WPM_JUMP = 120;

    public function inspectUpdate(TypingSession $session, array $payload, array $metrics): array
    {
        $flags = [];

        if (!empty($payload['paste_detected'])) {
            $flags[] = 'paste_attempt';
        }

        if (!empty($payload['tab_switched'])) {
            $flags[] = 'tab_switch';
        }

        if (!empty($payload['focus_lost'])) {
            $flags[] = 'focus_loss';
        }

        if (($metrics['wpm'] ?? 0) > self::MAX_WPM) {
            $flags[] = 'suspicious_wpm';
        }

        $previousWpm = (int) ($payload['previous_wpm'] ?? 0);
        $currentWpm = (int) ($metrics['wpm'] ?? 0);
        if ($currentWpm - $previousWpm > self::MAX_WPM_JUMP) {
            $flags[] = 'wpm_spike';
        }

        if (!empty($payload['bot_pattern'])) {
            $flags[] = 'bot_pattern';
        }

        if (!empty($payload['device_fingerprint']) && $session->device_fingerprint
            && $payload['device_fingerprint'] !== $session->device_fingerprint) {
            $flags[] = 'multiple_device';
        }

        return [
            'flagged' => !empty($flags),
            'flags' => array_values(array_unique($flags)),
        ];
    }

    public function persistFlags(TypingSession $session, array $flags, ?string $ipAddress = null): void
    {
        foreach ($flags as $flag) {
            AntiCheatLog::create([
                'contest_id' => $session->contest_id,
                'user_id' => $session->user_id,
                'event_type' => $this->mapEventType($flag),
                'ip_address' => $ipAddress,
                'severity' => in_array($flag, ['bot_pattern', 'multiple_device', 'paste_attempt'], true)
                    ? AntiCheatLog::SEVERITY_HIGH
                    : AntiCheatLog::SEVERITY_MEDIUM,
                'auto_flagged' => true,
                'metadata' => ['typing_flag' => $flag, 'typing_session_id' => $session->id],
                'logged_at' => now(),
            ]);
        }
    }

    private function mapEventType(string $flag): string
    {
        return match ($flag) {
            'paste_attempt' => AntiCheatLog::EVENT_PASTE_ATTEMPT,
            'tab_switch' => AntiCheatLog::EVENT_TAB_SWITCH,
            'multiple_device' => AntiCheatLog::EVENT_DEVICE_CHANGE,
            'wpm_spike', 'suspicious_wpm' => AntiCheatLog::EVENT_SUSPICIOUS_WPM,
            'bot_pattern' => AntiCheatLog::EVENT_AI_FLAG,
            default => AntiCheatLog::EVENT_TIMING_ANOMALY,
        };
    }
}
