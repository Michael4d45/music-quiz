<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class GameSessionsLobbyResponseData extends Data
{
    /**
     * @param Collection<int, GameSessionLobbySummaryData> $sessions
     */
    public function __construct(
        public Collection $sessions,
    ) {}
}
