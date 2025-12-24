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
 * AnswerVariant model for alternative correct answers.
 *
 * @property string $id
 * @property string $question_id
 * @property string|null $accepted_text
 * @property-read QuizQuestion $question
 * @property-read Collection<array-key, PlayerAnswer> $playerAnswers
 */
class AnswerVariant extends Model
{
    /** @use HasFactory<\Database\Factories\AnswerVariantFactory> */
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
        'accepted_text',
    ];

    /**
     * Get the question that owns this variant.
     *
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    /**
     * Get all player answers that matched this variant.
     *
     * @return HasMany<PlayerAnswer, $this>
     */
    public function playerAnswers(): HasMany
    {
        return $this->hasMany(PlayerAnswer::class, 'matched_variant_id');
    }
}
