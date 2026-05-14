<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Size;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class JoinGameSessionRequest extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Size(6)]
        public string $room_code,
        /**
         * Optional in-session display name (any player). Stored on the participant row;
         * when empty, the account name is shown instead.
         */
        #[Sometimes, Nullable, StringType, Max(64)]
        public null|string $display_name = null,
    ) {}
}
