<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SessionFinalScoreData extends Data
{
    public function __construct(
        public string $id,
        public string $session_id,
        public string $participant_id,
        public int $final_score,
        public int $final_rank,
        public int $questions_answered,
        public int $correct_answers,
        public null|int $average_response_time_ms,
        public int $longest_streak,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var GameSessionData|Lazy $session */
        #[AutoWhenLoadedLazy]
        public GameSessionData|Lazy $session,
        /** @var SessionParticipantData|Lazy $participant */
        #[AutoWhenLoadedLazy]
        public Lazy|SessionParticipantData $participant,
    ) {}
}
