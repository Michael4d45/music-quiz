<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * SessionParticipant model for users participating in game sessions.
 *
 * @property string $id
 * @property string $session_id
 * @property string|null $user_id
 * @property string|null $guest_name
 * @property string $role
 * @property int $current_total_score
 * @property bool $is_connected
 * @property Carbon $joined_at
 * @property Carbon|null $buzzed_in_at
 * @property-read GameSession $session
 * @property-read User|null $user
 * @property-read Collection<array-key, PlayerAnswer> $answers
 * @property-read Collection<array-key, SessionFinalScore> $finalScore
 */
class SessionParticipant extends Model
{
    /** @use HasFactory<\Database\Factories\SessionParticipantFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'guest_name',
        'role',
        'current_total_score',
        'is_connected',
        'joined_at',
        'buzzed_in_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<model-property<self>, mixed>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'current_total_score' => 'integer',
            'is_connected' => 'boolean',
            'joined_at' => 'datetime',
            'buzzed_in_at' => 'datetime',
        ];
    }

    /**
     * Get the session that this participant belongs to.
     *
     * @return BelongsTo<GameSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /**
     * Get the user associated with this participant.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all answers from this participant.
     *
     * @return HasMany<PlayerAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(PlayerAnswer::class, 'participant_id');
    }

    /**
     * Get the final score for this participant.
     *
     * @return HasOne<SessionFinalScore, $this>
     */
    public function finalScore(): HasOne
    {
        return $this->hasOne(SessionFinalScore::class, 'participant_id');
    }
}
