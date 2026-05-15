<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntiCheatLog extends Model
{
    protected $table = 'contest_anti_cheat_logs';
    public $timestamps = false;

    public const EVENT_TAB_SWITCH       = 'tab_switch';
    public const EVENT_PASTE_ATTEMPT    = 'paste_attempt';
    public const EVENT_SUSPICIOUS_WPM   = 'suspicious_wpm';
    public const EVENT_AI_FLAG          = 'ai_flag';
    public const EVENT_TIMING_ANOMALY   = 'timing_anomaly';
    public const EVENT_MULTI_SESSION    = 'multiple_sessions';
    public const EVENT_DEVICE_CHANGE    = 'device_change';
    public const EVENT_RAPID_CORRECTION = 'rapid_correction';

    public const SEVERITY_LOW    = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH   = 'high';

    protected $fillable = [
        'contest_id',
        'user_id',
        'event_type',
        'ip_address',
        'severity',
        'auto_flagged',
        'metadata',
        'logged_at',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'logged_at'    => 'datetime',
        'auto_flagged' => 'boolean',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
