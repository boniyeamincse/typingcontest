<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'typing_session_id',
        'contest_id',
        'user_id',
        'correct_words',
        'correct_characters',
        'total_characters',
        'errors',
        'cpm',
        'wpm',
        'accuracy',
        'score',
        'progress_percent',
        'duration_seconds',
        'is_disqualified',
        'disqualified_reason',
        'meta',
    ];

    protected $casts = [
        'correct_words' => 'integer',
        'correct_characters' => 'integer',
        'total_characters' => 'integer',
        'errors' => 'integer',
        'cpm' => 'integer',
        'wpm' => 'integer',
        'accuracy' => 'decimal:2',
        'score' => 'decimal:2',
        'progress_percent' => 'integer',
        'duration_seconds' => 'integer',
        'is_disqualified' => 'boolean',
        'meta' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TypingSession::class, 'typing_session_id');
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
