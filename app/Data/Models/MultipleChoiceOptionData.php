<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class MultipleChoiceOptionData extends Data
{
    public function __construct(
        public string $id,
        public string $question_id,
        public string $option_text,
        public bool $is_correct,
        public null|int $sort_order,
        /** @var QuizQuestionData|Optional $question */
        public Optional|QuizQuestionData $question,
        /** @var Collection<array-key,PlayerAnswerData>|Optional */
        public Collection|Optional $player_answers,
    ) {}
}
