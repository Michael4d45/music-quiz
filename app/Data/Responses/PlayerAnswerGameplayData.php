<?php

declare(strict_types=1);

namespace App\Data\Responses;

use Spatie\LaravelData\Data;

class PlayerAnswerGameplayData extends Data
{
    public function __construct(
        public string $id,
        public string $round_id,
        public string $participant_id,
        public string $participant_display_name,
        public null|string $submitted_text,
        public null|string $selected_option_id,
        public null|bool $is_correct,
        public null|int $points_awarded,
    ) {}
}
