<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * PlayerAnswer model for storing player responses to questions.
 *
 * @property string $id
 * @property string $round_id
 * @property string $participant_id
 * @property string|null $submitted_text
 * @property string|null $selected_option_id
 * @property string|null $matched_variant_id
 * @property bool $is_correct
 * @property int|null $response_time_ms
 * @property int|null $points_awarded
 * @property bool $host_override
 * @property Carbon|null $created_at
 * @property-read SessionRound $round
 * @property-read SessionParticipant $participant
 * @property-read MultipleChoiceOption|null $selectedOption
 * @property-read AnswerVariant|null $matchedVariant
 */
class PlayerAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\PlayerAnswerFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'round_id',
        'participant_id',
        'submitted_text',
        'selected_option_id',
        'matched_variant_id',
        'is_correct',
        'response_time_ms',
        'points_awarded',
        'host_override',
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
            'is_correct' => 'boolean',
            'response_time_ms' => 'integer',
            'points_awarded' => 'integer',
            'host_override' => 'boolean',
        ];
    }

    /**
     * Get the round that this answer belongs to.
     *
     * @return BelongsTo<SessionRound, $this>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(SessionRound::class, 'round_id');
    }

    /**
     * Get the participant who submitted this answer.
     *
     * @return BelongsTo<SessionParticipant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(SessionParticipant::class, 'participant_id');
    }

    /**
     * Get the selected multiple choice option.
     *
     * @return BelongsTo<MultipleChoiceOption, $this>
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(MultipleChoiceOption::class, 'selected_option_id');
    }

    /**
     * Get the matched answer variant.
     *
     * @return BelongsTo<AnswerVariant, $this>
     */
    public function matchedVariant(): BelongsTo
    {
        return $this->belongsTo(AnswerVariant::class, 'matched_variant_id');
    }
}
