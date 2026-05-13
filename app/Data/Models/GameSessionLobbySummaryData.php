<?php

declare(strict_types=1);

namespace App\Data\Models;

use Spatie\LaravelData\Data;

class GameSessionLobbySummaryData extends Data
{
    public function __construct(
        public string $id,
        public string $room_code,
        public string $host_display_name,
        public string $quiz_mode_name,
        public null|string $playlist_name,
        public int $max_players,
        public int $participant_count,
    ) {}
}
