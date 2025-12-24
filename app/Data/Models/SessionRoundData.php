<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SessionRoundData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public int $round_number,
        public string $question_id,
        public Carbon|null $started_at,
        public Carbon|null $ended_at,
        public string|null $first_buzzer_id,
        /** @var GameSessionData $session */
        #[AutoWhenLoadedLazy]
        public GameSessionData|Lazy $session,
        /** @var QuizQuestionData $question */
        #[AutoWhenLoadedLazy]
        public QuizQuestionData|Lazy $question,
        /** @var SessionParticipantData|null $first_buzzer */
        #[AutoWhenLoadedLazy("firstBuzzer")]
        public SessionParticipantData|Lazy|null $first_buzzer,
        /** @var Collection<array-key,PlayerAnswerData> $answers */
        #[AutoWhenLoadedLazy]
        public Collection|Lazy $answers,
    ) {}
}
