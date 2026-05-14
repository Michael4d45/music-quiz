<?php

declare(strict_types=1);

namespace App\Data\Events;

use Spatie\LaravelData\Data;

class GameSessionUpdatedData extends Data
{
    public function __construct(
        public string $session_id,
    ) {}
}
