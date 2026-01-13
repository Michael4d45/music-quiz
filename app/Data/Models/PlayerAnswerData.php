<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class PlayerAnswerData extends Data
{
    public function __construct(
        public string $id,
        public string $round_id,
        public string $participant_id,
        public null|string $submitted_text,
        public null|string $selected_option_id,
        public null|string $matched_variant_id,
        public bool $is_correct,
        public null|int $response_time_ms,
        public null|int $points_awarded,
        public bool $host_override,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var SessionRoundData|Lazy $round */
        #[AutoWhenLoadedLazy]
        public Lazy|SessionRoundData $round,
        /** @var SessionParticipantData|Lazy $participant */
        #[AutoWhenLoadedLazy]
        public Lazy|SessionParticipantData $participant,
        /** @var MultipleChoiceOptionData|null|Lazy $selected_option */
        #[AutoWhenLoadedLazy('selectedOption')]
        public Lazy|MultipleChoiceOptionData|null $selected_option,
        /** @var AnswerVariantData|null|Lazy $matched_variant */
        #[AutoWhenLoadedLazy('matchedVariant')]
        public AnswerVariantData|Lazy|null $matched_variant,
    ) {}
}
