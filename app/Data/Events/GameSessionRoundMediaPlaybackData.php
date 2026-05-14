<?php

declare(strict_types=1);

namespace App\Data\Events;

use Spatie\LaravelData\Data;

class GameSessionRoundMediaPlaybackData extends Data
{
    public function __construct(
        public string $session_id,
        public string $round_id,
        public bool $playing,
        public float $current_time_seconds,
        public int $server_seq,
    ) {}
}
