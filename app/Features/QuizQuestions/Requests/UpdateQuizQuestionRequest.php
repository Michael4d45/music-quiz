<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Requests;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use Spatie\LaravelData\Attributes\Validation\Enum as EnumRule;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class UpdateQuizQuestionRequest extends Data
{
    public function __construct(
        #[Sometimes, Nullable, Uuid, Exists('music_tracks', 'id')]
        public null|string $track_id = null,
        #[Sometimes, EnumRule(QuestionType::class)]
        public null|QuestionType $question_type = null,
        #[Sometimes, Nullable, StringType]
        public null|string $prompt_text = null,
        #[Sometimes, StringType, Max(2000)]
        public null|string $correct_answer = null,
        #[Sometimes, IntegerType, Min(0), Max(100_000)]
        public null|int $base_points = null,
        #[Sometimes, Nullable, IntegerType, Min(0)]
        public null|int $media_start_seconds = null,
        #[Sometimes, Nullable, IntegerType, Min(0)]
        public null|int $media_end_seconds = null,
        #[Sometimes, IntegerType, Min(1), Max(10)]
        public null|int $difficulty_level = null,
        #[Sometimes, EnumRule(Visibility::class)]
        public null|Visibility $visibility = null,
    ) {}
}
