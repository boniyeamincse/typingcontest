<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingProgressLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'typing_session_id',
        'sequence',
        'elapsed_ms',
        'wpm',
        'cpm',
        'accuracy',
        'errors',
        'progress_percent',
        'snapshot',
        'logged_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'elapsed_ms' => 'integer',
        'wpm' => 'integer',
        'cpm' => 'integer',
        'accuracy' => 'decimal:2',
        'errors' => 'integer',
        'progress_percent' => 'integer',
        'snapshot' => 'array',
        'logged_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TypingSession::class, 'typing_session_id');
    }
}
