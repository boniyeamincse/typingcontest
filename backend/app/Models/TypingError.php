<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingError extends Model
{
    use HasFactory;

    protected $fillable = [
        'typing_session_id',
        'sequence',
        'word_index',
        'char_index',
        'expected_char',
        'typed_char',
        'error_type',
        'detected_at',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'word_index' => 'integer',
        'char_index' => 'integer',
        'detected_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TypingSession::class, 'typing_session_id');
    }
}
