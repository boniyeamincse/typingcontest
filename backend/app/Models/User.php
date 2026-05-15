<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'username', 'email', 'password', 'avatar', 'country',
    'plan_type', 'subscription_status', 'subscription_end_date',
    'xp_points', 'global_rank', 'total_wpm', 'accuracy_avg', 'is_banned'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'subscription_end_date' => 'datetime',
            'password' => 'hashed',
            'xp_points' => 'integer',
            'global_rank' => 'integer',
            'total_wpm' => 'integer',
            'accuracy_avg' => 'decimal:2',
            'is_banned' => 'boolean',
        ];
    }

    // JWT Subject Implementation
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class, 'created_by');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges', 'user_id', 'badge_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function leaderboards(): HasMany
    {
        return $this->hasMany(Leaderboard::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}

