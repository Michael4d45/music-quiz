<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class UpdateGameSessionRequest extends Data
{
    public function __construct(
        #[Sometimes, BooleanType]
        public null|bool $is_public = null,
        #[Sometimes, IntegerType, Min(2), Max(50)]
        public null|int $max_players = null,
        #[Sometimes, Nullable, Uuid, Exists('playlists', 'id')]
        public null|string $playlist_id = null,
    ) {}
}
