<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    use HasFactory;
    protected $table = 'badges';

    protected $fillable = [
        'name',
        'slug',
        'icon_url',
        'description',
        'requirement_type',
        'requirement_value',
        'is_premium',
    ];

    protected $casts = [
        'requirement_value' => 'integer',
        'is_premium' => 'boolean',
    ];

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }
}
