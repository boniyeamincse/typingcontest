<?php

namespace App\Services\Contest;

class ScoreService
{
    /**
     * Calculate WPM (Words Per Minute).
     *
     * Standard definition: 1 word = 5 characters.
     * WPM = (characters_typed / 5) / elapsed_minutes
     */
    public function calculateWpm(int $charactersTyped, float $elapsedSeconds): int
    {
        if ($elapsedSeconds <= 0) {
            return 0;
        }

        $minutes = $elapsedSeconds / 60;
        $words   = $charactersTyped / 5;

        return (int) round($words / $minutes);
    }

    /**
     * Calculate accuracy as a percentage.
     *
     * accuracy = ((total_keystrokes - errors) / total_keystrokes) * 100
     * Clamped to [0, 100].
     */
    public function calculateAccuracy(int $totalKeystrokes, int $errors): float
    {
        if ($totalKeystrokes <= 0) {
            return 0.0;
        }

        $correctKeystrokes = max(0, $totalKeystrokes - $errors);
        $accuracy          = ($correctKeystrokes / $totalKeystrokes) * 100;

        return round(min(100.0, max(0.0, $accuracy)), 2);
    }

    /**
     * Final score formula: Score = (WPM × accuracy / 100) − errors
     * Guaranteed non-negative.
     */
    public function calculateScore(int $wpm, float $accuracy, int $errors): float
    {
        $score = ($wpm * ($accuracy / 100)) - $errors;

        return round(max(0.0, $score), 2);
    }

    /**
     * Convenience: compute all metrics at once from raw submission data.
     *
     * @param  array{characters_typed: int, errors: int, elapsed_seconds: float} $data
     * @return array{wpm: int, accuracy: float, errors: int, score: float}
     */
    public function compute(array $data): array
    {
        $wpm      = $this->calculateWpm($data['characters_typed'], $data['elapsed_seconds']);
        $accuracy = $this->calculateAccuracy($data['characters_typed'], $data['errors']);
        $score    = $this->calculateScore($wpm, $accuracy, $data['errors']);

        return [
            'wpm'      => $wpm,
            'accuracy' => $accuracy,
            'errors'   => $data['errors'],
            'score'    => $score,
        ];
    }
}
