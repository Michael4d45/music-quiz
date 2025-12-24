<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * GameSession model for quiz game sessions.
 *
 * @property string $id
 * @property string $host_id
 * @property string $room_code
 * @property SessionStatus $status
 * @property string $quiz_mode_id
 * @property string $scoring_rule_id
 * @property string|null $playlist_id
 * @property int $max_players
 * @property Carbon|null $created_at
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property-read User $host
 * @property-read QuizMode $quizMode
 * @property-read ScoringRule $scoringRule
 * @property-read Playlist|null $playlist
 * @property-read Collection<array-key, SessionParticipant> $participants
 * @property-read Collection<array-key, SessionRound> $rounds
 * @property-read Collection<array-key, SessionEvent> $events
 * @property-read Collection<array-key, SessionFinalScore> $finalScores
 */
class GameSession extends Model
{
    /** @use HasFactory<\Database\Factories\GameSessionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'host_id',
        'room_code',
        'status',
        'quiz_mode_id',
        'scoring_rule_id',
        'playlist_id',
        'max_players',
        'started_at',
        'ended_at',
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
            'status' => SessionStatus::class,
            'max_players' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the host user for this session.
     *
     * @return BelongsTo<User, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get the quiz mode for this session.
     *
     * @return BelongsTo<QuizMode, $this>
     */
    public function quizMode(): BelongsTo
    {
        return $this->belongsTo(QuizMode::class, 'quiz_mode_id');
    }

    /**
     * Get the scoring rule for this session.
     *
     * @return BelongsTo<ScoringRule, $this>
     */
    public function scoringRule(): BelongsTo
    {
        return $this->belongsTo(ScoringRule::class, 'scoring_rule_id');
    }

    /**
     * Get the playlist used for this session.
     *
     * @return BelongsTo<Playlist, $this>
     */
    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class, 'playlist_id');
    }

    /**
     * Get all participants in this session.
     *
     * @return HasMany<SessionParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'session_id');
    }

    /**
     * Get all rounds in this session.
     *
     * @return HasMany<SessionRound, $this>
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(SessionRound::class, 'session_id')->orderBy('round_number');
    }

    /**
     * Get all events for this session.
     *
     * @return HasMany<SessionEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(SessionEvent::class, 'session_id')->orderBy('created_at');
    }

    /**
     * Get all final scores for this session.
     *
     * @return HasMany<SessionFinalScore, $this>
     */
    public function finalScores(): HasMany
    {
        return $this->hasMany(SessionFinalScore::class, 'session_id')->orderByDesc('final_score');
    }
}
