<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\Visibility;
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
 * @property string|null $user_id
 * @property string|null $track_id
 * @property QuestionType $question_type
 * @property string|null $prompt_text
 * @property string $correct_answer
 * @property int $base_points
 * @property int|null $media_start_seconds
 * @property int|null $media_end_seconds
 * @property int $difficulty_level
 * @property Visibility $visibility
 * @property string|null $rich_prompt_text
 * @property string|null $explanation
 * @property array<string, mixed>|null $hints
 * @property bool $is_draft
 * @property Carbon|null $last_tested_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read User|null $user
 * @property-read MusicTrack|null $track
 * @property-read Collection<array-key,AnswerVariant> $answerVariants
 * @property-read Collection<array-key,MultipleChoiceOption> $multipleChoiceOptions
 * @property-read Collection<array-key,SessionRound> $sessionRounds
 * @property-read Collection<array-key,PlaylistItem> $playlistItems
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
        'user_id',
        'track_id',
        'question_type',
        'prompt_text',
        'rich_prompt_text',
        'explanation',
        'hints',
        'correct_answer',
        'base_points',
        'media_start_seconds',
        'media_end_seconds',
        'difficulty_level',
        'visibility',
        'is_draft',
        'last_tested_at',
    ];

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
     * Get all playlist items that reference this question.
     *
     * @return HasMany<PlaylistItem, $this>
     */
    public function playlistItems(): HasMany
    {
        return $this->hasMany(PlaylistItem::class, 'question_id');
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
     * Get the user that owns this question.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
            'visibility' => Visibility::class,
            'hints' => 'array',
            'is_draft' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }
}
