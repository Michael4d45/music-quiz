<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MultipleChoiceOption model for multiple choice question options.
 *
 * @property string $id
 * @property string $question_id
 * @property string $option_text
 * @property bool $is_correct
 * @property int|null $sort_order
 * @property-read QuizQuestion $question
 * @property-read Collection<array-key, PlayerAnswer> $playerAnswers
 */
class MultipleChoiceOption extends Model
{
    /** @use HasFactory<\Database\Factories\MultipleChoiceOptionFactory> */
    use HasFactory;

    use HasUuids;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'sort_order',
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
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the question that owns this option.
     *
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    /**
     * Get all player answers that selected this option.
     *
     * @return HasMany<PlayerAnswer, $this>
     */
    public function playerAnswers(): HasMany
    {
        return $this->hasMany(PlayerAnswer::class, 'selected_option_id');
    }
}
