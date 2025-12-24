<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * User model for authentication and user management.
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_admin
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Collection<array-key, GameSession> $gameSessions
 * @property-read Collection<array-key, SessionParticipant> $participants
 * @property-read Collection<array-key, UserStatistic> $statistics
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get all game sessions hosted by this user.
     *
     * @return HasMany<GameSession, $this>
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'host_id');
    }

    /**
     * Get all session participations for this user.
     *
     * @return HasMany<SessionParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'user_id');
    }

    /**
     * Get all statistics for this user.
     *
     * @return HasMany<UserStatistic, $this>
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(UserStatistic::class, 'user_id');
    }
}
