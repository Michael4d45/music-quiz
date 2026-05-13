<?php

declare(strict_types=1);

namespace App\Data\Events;

use Spatie\LaravelData\Data;

class GameSessionParticipantJoinedData extends Data
{
    public function __construct(
        public string $session_id,
        public string $participant_id,
        public int $participant_count,
    ) {}
}
