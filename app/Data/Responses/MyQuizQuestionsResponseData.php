<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\QuizQuestionData;
use Spatie\LaravelData\Data;

class MyQuizQuestionsResponseData extends Data
{
    /**
     * @param list<QuizQuestionData> $questions
     */
    public function __construct(
        public array $questions,
    ) {}
}
