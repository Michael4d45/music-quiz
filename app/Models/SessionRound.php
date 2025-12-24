<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * SessionRound model for individual rounds in game sessions.
 *
 * @property string $id
 * @property string $session_id
 * @property int $round_number
 * @property string $question_id
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property string|null $first_buzzer_id
 * @property-read GameSession $session
 * @property-read QuizQuestion $question
 * @property-read SessionParticipant|null $firstBuzzer
 * @property-read Collection<array-key, PlayerAnswer> $answers
 */
class SessionRound extends Model
{
    /** @use HasFactory<\Database\Factories\SessionRoundFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'session_id',
        'round_number',
        'question_id',
        'started_at',
        'ended_at',
        'first_buzzer_id',
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
            'round_number' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the session that owns this round.
     *
     * @return BelongsTo<GameSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /**
     * Get the question for this round.
     *
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    /**
     * Get the first buzzer participant for this round.
     *
     * @return BelongsTo<SessionParticipant, $this>
     */
    public function firstBuzzer(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'first_buzzer_id');
    }

    /**
     * Get all answers for this round.
     *
     * @return HasMany<PlayerAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(PlayerAnswer::class, 'round_id');
    }
}
