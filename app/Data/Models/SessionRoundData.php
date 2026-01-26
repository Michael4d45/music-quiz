<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SessionRoundData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public int $round_number,
        public string $question_id,
        public null|Carbon $started_at,
        public null|Carbon $ended_at,
        public null|string $first_buzzer_id,
        /** @var GameSessionData|Optional $session */
        public GameSessionData|Optional $session,
        /** @var QuizQuestionData|Optional $question */
        public Optional|QuizQuestionData $question,
        /** @var SessionParticipantData|null|Optional $first_buzzer */
        public Optional|SessionParticipantData|null $first_buzzer,
        /** @var Collection<array-key,PlayerAnswerData>|Optional */
        public Collection|Optional $answers,
    ) {}
}
