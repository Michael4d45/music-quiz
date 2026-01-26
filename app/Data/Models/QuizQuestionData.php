<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class QuizQuestionData extends Data
{
    public function __construct(
        public string $id,
        public null|string $user_id,
        public null|string $track_id,
        public QuestionType $question_type,
        public null|string $prompt_text,
        public string $correct_answer,
        public int $base_points,
        public null|int $media_start_seconds,
        public null|int $media_end_seconds,
        public int $difficulty_level,
        public Visibility $visibility,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var UserData|null|Optional $user */
        public Optional|UserData|null $user,
        /** @var MusicTrackData|null|Optional $track */
        public Optional|MusicTrackData|null $track,
        /** @var Collection<array-key,AnswerVariantData>|Optional */
        public Collection|Optional $answer_variants,
        /** @var Collection<array-key,MultipleChoiceOptionData>|Optional */
        public Collection|Optional $multiple_choice_options,
        /** @var Collection<array-key,SessionRoundData>|Optional */
        public Collection|Optional $session_rounds,
        /** @var Collection<array-key,PlaylistItemData>|Optional */
        public Collection|Optional $playlist_items,
    ) {}
}
