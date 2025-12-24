<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SessionFinalScore model for final scores of participants.
 *
 * @property string $id
 * @property string $session_id
 * @property string $participant_id
 * @property int $final_score
 * @property int $final_rank
 * @property int $questions_answered
 * @property int $correct_answers
 * @property int|null $average_response_time_ms
 * @property int $longest_streak
 * @property Carbon|null $created_at
 * @property-read GameSession $session
 * @property-read SessionParticipant $participant
 */
class SessionFinalScore extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFinalScoreFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'session_id',
        'participant_id',
        'final_score',
        'final_rank',
        'questions_answered',
        'correct_answers',
        'average_response_time_ms',
        'longest_streak',
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
            'final_score' => 'integer',
            'final_rank' => 'integer',
            'questions_answered' => 'integer',
            'correct_answers' => 'integer',
            'average_response_time_ms' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    /**
     * Get the session that this final score belongs to.
     *
     * @return BelongsTo<GameSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /**
     * Get the participant that this final score belongs to.
     *
     * @return BelongsTo<SessionParticipant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }
}
