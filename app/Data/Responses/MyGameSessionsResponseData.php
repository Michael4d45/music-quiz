<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\GameSessionData;
use Spatie\LaravelData\Data;

class MyGameSessionsResponseData extends Data
{
    /**
     * @param list<GameSessionData> $sessions
     */
    public function __construct(
        public array $sessions,
    ) {}
}
