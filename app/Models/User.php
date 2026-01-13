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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

/**
 * User model for authentication and user management.
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password
 * @property bool $is_guest
 * @property bool $is_admin
 * @property bool $is_google_verified
 * @property string|null $google_id
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
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
    use HasFactory, Notifiable, HasApiTokens;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_guest',
        'is_admin',
        'google_id',
        'verified_google_email',
        'email_verified_at',
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

    public function canAccessPanel(Panel $panel): bool
    {
        return !$this->is_guest && $this->is_admin;
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
     * Merge guest user data to the real user and delete the guest.
     */
    public static function mergeGuestData(self $guest, self $user): void
    {
        DB::transaction(function () use ($guest, $user) {
            // Transfer game sessions hosted by guest
            GameSession::where('host_id', $guest->id)->update([
                'host_id' => $user->id,
            ]);

            // Transfer session participations
            SessionParticipant::where('user_id', $guest->id)->update([
                'user_id' => $user->id,
            ]);

            // Transfer user statistics
            UserStatistic::where('user_id', $guest->id)->update([
                'user_id' => $user->id,
            ]);

            // Transfer playlists
            Playlist::where('user_id', $guest->id)->update([
                'user_id' => $user->id,
            ]);

            // Transfer quiz questions
            QuizQuestion::where('user_id', $guest->id)->update([
                'user_id' => $user->id,
            ]);

            // Transfer music tracks
            MusicTrack::where('user_id', $guest->id)->update([
                'user_id' => $user->id,
            ]);

            // Delete the guest user
            $guest->delete();
        });
    }

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
            'is_guest' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }
}
