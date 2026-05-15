<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'typing_session_id',
        'sequence',
        'cursor_position',
        'correct_characters',
        'total_characters',
        'errors',
        'progress_percent',
        'payload',
        'captured_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'cursor_position' => 'integer',
        'correct_characters' => 'integer',
        'total_characters' => 'integer',
        'errors' => 'integer',
        'progress_percent' => 'integer',
        'payload' => 'array',
        'captured_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TypingSession::class, 'typing_session_id');
    }
}
