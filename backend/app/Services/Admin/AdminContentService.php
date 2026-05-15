<?php

namespace App\Services\Admin;

use App\Models\TypingText;

class AdminContentService
{
    public function list(array $filters)
    {
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return TypingText::query()
            ->when($filters['language'] ?? null, fn ($q, $language) => $q->where('language', $language))
            ->when($filters['difficulty'] ?? null, fn ($q, $difficulty) => $q->where('difficulty', $difficulty))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $payload): TypingText
    {
        $payload['word_count'] = str_word_count((string) ($payload['content'] ?? ''));

        return TypingText::create($payload);
    }

    public function update(TypingText $typingText, array $payload): TypingText
    {
        if (isset($payload['content'])) {
            $payload['word_count'] = str_word_count((string) $payload['content']);
        }

        $typingText->update($payload);

        return $typingText->refresh();
    }

    public function delete(TypingText $typingText): void
    {
        $typingText->delete();
    }
}
