<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AnswerVariantData extends Data
{
    public function __construct(
        public string $id,
        public string $question_id,
        public null|string $accepted_text,
        /** @var QuizQuestionData|Optional $question */
        public Optional|QuizQuestionData $question,
        /** @var Collection<array-key,PlayerAnswerData>|Optional */
        public Collection|Optional $player_answers,
    ) {}
}
