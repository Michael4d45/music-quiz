<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * User model for storing user information and authentication.
 *
 * @property string $id
 *
 * @property string|null $name
 *
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 *
 * @property string|null $google_id
 * @property string|null $verified_google_email
 *
 * @property bool $is_admin
 * @property bool $is_guest
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<array-key,GameSession> $gameSessions
 * @property-read Collection<array-key,SessionParticipant> $participants
 * @property-read Collection<array-key,UserStatistic> $statistics
 * @property-read Collection<array-key,Playlist> $playlists
 * @property-read Collection<array-key,QuizQuestion> $quizQuestions
 * @property-read Collection<array-key,MusicTrack> $musicTracks
 */
class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'name',

        'email',
        'password',
        'remember_token',
        'email_verified_at',

        'google_id',
        'verified_google_email',

        'is_admin',
        'is_guest',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<model-property<self>>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<model-property<self>,mixed>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_guest' => 'boolean',
        ];
    }

    /**
     * Determine if the user should verify their email.
     */
    public function hasVerifiedEmail(): bool
    {
        return !$this->is_guest && !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        if ($this->is_guest) {
            return false;
        }

        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (!$this->is_guest) {
            // Use the default implementation
            parent::sendEmailVerificationNotification();
        }
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

    /**
     * Get all playlists for this user.
     *
     * @return HasMany<Playlist, $this>
     */
    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class, 'user_id');
    }

    /**
     * Get all quiz questions created by this user.
     *
     * @return HasMany<QuizQuestion, $this>
     */
    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'user_id');
    }

    /**
     * Get all music tracks created by this user.
     *
     * @return HasMany<MusicTrack, $this>
     */
    public function musicTracks(): HasMany
    {
        return $this->hasMany(MusicTrack::class, 'user_id');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name ?? 'User')
            ->explode(' ')
            ->take(2)
            ->map(static fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
