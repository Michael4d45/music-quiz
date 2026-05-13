<?php

declare(strict_types=1);

namespace App\Features\Playlists\Requests;

use App\Enums\PlaylistStatus;
use App\Enums\Visibility;
use Spatie\LaravelData\Attributes\Validation\Enum as EnumRule;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdatePlaylistRequest extends Data
{
    public function __construct(
        #[Sometimes, StringType, Max(255)]
        public null|string $name = null,
        #[Sometimes, Nullable, StringType]
        public null|string $description = null,
        #[Sometimes, EnumRule(PlaylistStatus::class)]
        public null|PlaylistStatus $status = null,
        #[Sometimes, EnumRule(Visibility::class)]
        public null|Visibility $visibility = null,
    ) {}
}
