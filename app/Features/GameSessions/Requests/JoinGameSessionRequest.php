<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class JoinGameSessionRequest extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Size(6)]
        public string $room_code,
    ) {}
}
