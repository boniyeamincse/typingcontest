<?php

namespace App\Services\Typing;

class TypingMetricService
{
    public function analyze(string $expectedText, string $typedText, int $elapsedMs): array
    {
        $totalCharacters = mb_strlen($typedText);
        $expectedLength = max(1, mb_strlen($expectedText));

        [$correctCharacters, $errorCount] = $this->characterStats($expectedText, $typedText);
        $correctWords = $this->correctWordCount($expectedText, $typedText);

        $elapsedMinutes = max($elapsedMs / 60000, 1 / 60000);

        $wpm = (int) round($correctWords / $elapsedMinutes);
        $cpm = (int) round($totalCharacters / $elapsedMinutes);
        $accuracy = round(($totalCharacters > 0 ? ($correctCharacters / $totalCharacters) : 0) * 100, 2);
        $progressPercent = (int) min(100, round(($correctCharacters / $expectedLength) * 100));
        $score = round(max(0, ($wpm * $accuracy) - $errorCount), 2);

        return [
            'correct_words' => $correctWords,
            'correct_characters' => $correctCharacters,
            'total_characters' => $totalCharacters,
            'errors' => $errorCount,
            'wpm' => max(0, $wpm),
            'cpm' => max(0, $cpm),
            'accuracy' => max(0, min(100, $accuracy)),
            'progress_percent' => $progressPercent,
            'score' => $score,
            'duration_seconds' => (int) round($elapsedMs / 1000),
        ];
    }

    private function characterStats(string $expectedText, string $typedText): array
    {
        $expectedChars = preg_split('//u', $expectedText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $typedChars = preg_split('//u', $typedText, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $maxLen = max(count($expectedChars), count($typedChars));
        $correct = 0;
        $errors = 0;

        for ($i = 0; $i < $maxLen; $i++) {
            $expected = $expectedChars[$i] ?? null;
            $typed = $typedChars[$i] ?? null;

            if ($typed === null) {
                continue;
            }

            if ($expected !== null && $typed === $expected) {
                $correct++;
            } else {
                $errors++;
            }
        }

        return [$correct, $errors];
    }

    private function correctWordCount(string $expectedText, string $typedText): int
    {
        $expectedWords = preg_split('/\s+/u', trim($expectedText)) ?: [];
        $typedWords = preg_split('/\s+/u', trim($typedText)) ?: [];

        $count = 0;
        $max = min(count($expectedWords), count($typedWords));

        for ($i = 0; $i < $max; $i++) {
            if ($typedWords[$i] === $expectedWords[$i]) {
                $count++;
            }
        }

        return $count;
    }
}
