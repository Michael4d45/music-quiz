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
        public string|null $submitted_text,
        public string|null $selected_option_id,
        public string|null $matched_variant_id,
        public bool $is_correct,
        public int|null $response_time_ms,
        public int|null $points_awarded,
        public bool $host_override,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var SessionRoundData $round */
        #[AutoWhenLoadedLazy]
        public Lazy|SessionRoundData $round,
        /** @var SessionParticipantData $participant */
        #[AutoWhenLoadedLazy]
        public Lazy|SessionParticipantData $participant,
        /** @var MultipleChoiceOptionData|null $selected_option */
        #[AutoWhenLoadedLazy('selectedOption')]
        public Lazy|MultipleChoiceOptionData|null $selected_option,
        /** @var AnswerVariantData|null $matched_variant */
        #[AutoWhenLoadedLazy('matchedVariant')]
        public AnswerVariantData|Lazy|null $matched_variant,
    ) {}
}
