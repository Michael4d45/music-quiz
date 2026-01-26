<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

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
        /** @var SessionRoundData|Optional $round */
        public Optional|SessionRoundData $round,
        /** @var SessionParticipantData|Optional $participant */
        public Optional|SessionParticipantData $participant,
        /** @var MultipleChoiceOptionData|null|Optional $selected_option */
        public Optional|MultipleChoiceOptionData|null $selected_option,
        /** @var AnswerVariantData|null|Optional $matched_variant */
        public AnswerVariantData|Optional|null $matched_variant,
    ) {}
}
