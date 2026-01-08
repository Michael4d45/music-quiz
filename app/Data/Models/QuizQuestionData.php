<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class QuizQuestionData extends Data
{
    public function __construct(
        public string $id,
        public string|null $user_id,
        public string|null $track_id,
        public QuestionType $question_type,
        public string|null $prompt_text,
        public string $correct_answer,
        public int $base_points,
        public int|null $media_start_seconds,
        public int|null $media_end_seconds,
        public int $difficulty_level,
        public Visibility $visibility,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var UserData|null $user */
        #[AutoWhenLoadedLazy]
        public Optional|UserData|null $user,
        /** @var MusicTrackData|null $track */
        #[AutoWhenLoadedLazy]
        public Optional|MusicTrackData|null $track,
        /** @var Collection<array-key,AnswerVariantData> $answer_variants */
        #[AutoWhenLoadedLazy('answerVariants')]
        public Collection|Optional $answer_variants,
        /** @var Collection<array-key,MultipleChoiceOptionData> $multiple_choice_options */
        #[AutoWhenLoadedLazy('multipleChoiceOptions')]
        public Collection|Optional $multiple_choice_options,
        /** @var Collection<array-key,SessionRoundData> $session_rounds */
        #[AutoWhenLoadedLazy('sessionRounds')]
        public Collection|Optional $session_rounds,
        /** @var Collection<array-key,PlaylistItemData> $playlist_items */
        #[AutoWhenLoadedLazy('playlistItems')]
        public Collection|Optional $playlist_items,
    ) {}
}
