<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypingText extends Model
{
    protected $table = 'typing_texts';

    protected $fillable = [
        'content',
        'language',
        'word_count',
        'difficulty',
        'source_label',
    ];

    protected $casts = [
        'word_count' => 'integer',
    ];

    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class, 'typing_text_id');
    }
}
