<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Enums\QuestionType;
use Spatie\LaravelData\Data;

class QuizQuestionGameplayData extends Data
{
    /**
     * @param list<MultipleChoiceOptionGameplayData> $multiple_choice_options
     * @param list<AnswerVariantGameplayData> $answer_variants
     */
    public function __construct(
        public string $id,
        public QuestionType $question_type,
        public null|string $prompt_text,
        public null|string $correct_answer,
        public int $base_points,
        public null|int $media_start_seconds,
        public null|int $media_end_seconds,
        public array $multiple_choice_options,
        public array $answer_variants,
    ) {}
}
