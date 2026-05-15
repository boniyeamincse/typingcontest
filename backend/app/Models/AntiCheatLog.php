<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntiCheatLog extends Model
{
    protected $table = 'contest_anti_cheat_logs';
    public $timestamps = false;

    protected $fillable = [
        'contest_id',
        'user_id',
        'event_type',
        'metadata',
        'logged_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'logged_at' => 'datetime',
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
