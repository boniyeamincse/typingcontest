<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'username', 'email', 'password', 'avatar', 'country',
    'plan_type', 'subscription_status', 'subscription_end_date',
    'xp_points', 'global_rank', 'total_wpm', 'accuracy_avg', 'is_banned',
    'last_login_at', 'last_login_ip', 'last_login_user_agent',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
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
            'last_login_at' => 'datetime',
        ];
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

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function loginActivities(): HasMany
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function twoFactorAuth(): HasOne
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(UserStatistic::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }
}

