<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class CreateGameSessionRequest extends Data
{
    public function __construct(
        #[Required]
        #[Uuid]
        #[Exists('quiz_modes', 'id')]
        public string $quiz_mode_id,

        #[Required]
        #[Uuid]
        #[Exists('scoring_rules', 'id')]
        public string $scoring_rule_id,

        #[Nullable]
        #[Uuid]
        #[Exists('playlists', 'id')]
        public null|string $playlist_id,

        #[Required]
        #[Min(2)]
        #[Max(50)]
        public int $max_players,

        #[Required]
        public bool $is_public,
    ) {}
}
