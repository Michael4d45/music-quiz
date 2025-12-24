<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * QuizQuestion model for quiz questions and answers.
 *
 * @property string $id
 * @property string|null $track_id
 * @property string $question_type
 * @property string|null $prompt_text
 * @property string $correct_answer
 * @property int $base_points
 * @property int|null $media_start_seconds
 * @property int|null $media_end_seconds
 * @property int $difficulty_level
 * @property Carbon|null $created_at
 * @property-read MusicTrack|null $track
 * @property-read Collection<array-key, AnswerVariant> $answerVariants
 * @property-read Collection<array-key, MultipleChoiceOption> $multipleChoiceOptions
 * @property-read Collection<array-key, SessionRound> $sessionRounds
 * @property-read Collection<array-key, PlaylistItem> $playlistItems
 */
class QuizQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\QuizQuestionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'track_id',
        'question_type',
        'prompt_text',
        'correct_answer',
        'base_points',
        'media_start_seconds',
        'media_end_seconds',
        'difficulty_level',
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
            'question_type' => QuestionType::class,
            'base_points' => 'integer',
            'media_start_seconds' => 'integer',
            'media_end_seconds' => 'integer',
            'difficulty_level' => 'integer',
        ];
    }

    /**
     * Get the track that owns this question.
     *
     * @return BelongsTo<MusicTrack, $this>
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(MusicTrack::class, 'track_id');
    }

    /**
     * Get all answer variants for this question.
     *
     * @return HasMany<AnswerVariant, $this>
     */
    public function answerVariants(): HasMany
    {
        return $this->hasMany(AnswerVariant::class, 'question_id');
    }

    /**
     * Get all multiple choice options for this question.
     *
     * @return HasMany<MultipleChoiceOption, $this>
     */
    public function multipleChoiceOptions(): HasMany
    {
        return $this->hasMany(MultipleChoiceOption::class, 'question_id');
    }

    /**
     * Get all session rounds that use this question.
     *
     * @return HasMany<SessionRound, $this>
     */
    public function sessionRounds(): HasMany
    {
        return $this->hasMany(SessionRound::class, 'question_id');
    }

    /**
     * Get all playlist items that reference this question.
     *
     * @return HasMany<PlaylistItem, $this>
     */
    public function playlistItems(): HasMany
    {
        return $this->hasMany(PlaylistItem::class, 'question_id');
    }
}
