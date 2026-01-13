<?php

declare(strict_types=1);

namespace App\Data\Response;

use App\Data\Models\QuizQuestionData;
use App\Data\Models\ScoringRuleData;
use App\Data\Models\SessionParticipantData;
use App\Data\Models\SessionRoundData;
use Spatie\LaravelData\Data;

class SessionPlayResponse extends Data
{
    public function __construct(
        public null|SessionRoundData $round,
        public null|QuizQuestionData $question,
        public null|SessionParticipantData $participant,
        public null|ScoringRuleData $scoring_rule = null,
    ) {}
}
