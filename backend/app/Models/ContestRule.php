<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(schema="ContestRule")
 */
class ContestRule extends Model
{
    protected $table = 'contest_rules';

    protected $fillable = [
        'contest_id',
        'duration_minutes',
        'max_wpm_threshold',
        'allow_paste',
        'allow_late_join',
        'late_join_grace_seconds',
        'auto_submit_on_timeout',
        'anti_cheat_enabled',
        'track_keystrokes',
        'max_tab_switches',
    ];

    protected $casts = [
        'duration_minutes'        => 'integer',
        'max_wpm_threshold'       => 'integer',
        'allow_paste'             => 'boolean',
        'allow_late_join'         => 'boolean',
        'late_join_grace_seconds' => 'integer',
        'auto_submit_on_timeout'  => 'boolean',
        'anti_cheat_enabled'      => 'boolean',
        'track_keystrokes'        => 'boolean',
        'max_tab_switches'        => 'integer',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }
}
