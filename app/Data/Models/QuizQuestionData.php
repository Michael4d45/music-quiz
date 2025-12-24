<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\QuestionType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class QuizQuestionData extends Data
{
    public function __construct(
        public string $id,
        public string|null $track_id,
        public QuestionType $question_type,
        public string|null $prompt_text,
        public string $correct_answer,
        public int $base_points,
        public int|null $media_start_seconds,
        public int|null $media_end_seconds,
        public int $difficulty_level,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var MusicTrackData|null $track */
        #[AutoWhenLoadedLazy]
        public MusicTrackData|Lazy|null $track,
        /** @var Collection<array-key,AnswerVariantData> $answer_variants */
        #[AutoWhenLoadedLazy("answerVariants")]
        public Collection|Lazy $answer_variants,
        /** @var Collection<array-key,MultipleChoiceOptionData> $multiple_choice_options */
        #[AutoWhenLoadedLazy("multipleChoiceOptions")]
        public Collection|Lazy $multiple_choice_options,
        /** @var Collection<array-key,SessionRoundData> $session_rounds */
        #[AutoWhenLoadedLazy("sessionRounds")]
        public Collection|Lazy $session_rounds,
        /** @var Collection<array-key,PlaylistItemData> $playlist_items */
        #[AutoWhenLoadedLazy("playlistItems")]
        public Collection|Lazy $playlist_items,
    ) {}
}
