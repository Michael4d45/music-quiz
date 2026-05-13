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
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class StoreQuizQuestionRequest extends Data
{
    public function __construct(
        #[Required, EnumRule(QuestionType::class)]
        public QuestionType $question_type,
        #[Required, StringType, Max(2000)]
        public string $correct_answer,
        #[Required, IntegerType, Min(0), Max(100_000)]
        public int $base_points,
        #[Required, IntegerType, Min(1), Max(10)]
        public int $difficulty_level,
        #[Nullable, Uuid, Exists('music_tracks', 'id')]
        public null|string $track_id = null,
        #[Nullable, StringType]
        public null|string $prompt_text = null,
        #[Nullable, IntegerType, Min(0)]
        public null|int $media_start_seconds = null,
        #[Nullable, IntegerType, Min(0)]
        public null|int $media_end_seconds = null,
        #[Sometimes, EnumRule(Visibility::class)]
        public null|Visibility $visibility = null,
    ) {}
}
