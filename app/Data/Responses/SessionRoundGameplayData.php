<?php

declare(strict_types=1);

namespace App\Data\Responses;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class SessionRoundGameplayData extends Data
{
    /**
     * @param list<PlayerAnswerGameplayData> $answers
     */
    public function __construct(
        public string $id,
        public string $session_id,
        public int $round_number,
        public string $question_id,
        public null|Carbon $started_at,
        public null|Carbon $ended_at,
        public null|string $first_buzzer_id,
        public QuizQuestionGameplayData $question,
        public array $answers,
    ) {}
}
