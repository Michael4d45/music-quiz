<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class AnswerVariantData extends Data
{
    public function __construct(
        public string $id,
        public string $question_id,
        public string|null $accepted_text,
        /** @var QuizQuestionData $question */
        #[AutoWhenLoadedLazy]
        public QuizQuestionData|Lazy $question,
        /** @var Collection<array-key,PlayerAnswerData> $player_answers */
        #[AutoWhenLoadedLazy("playerAnswers")]
        public Collection|Lazy $player_answers,
    ) {}
}
