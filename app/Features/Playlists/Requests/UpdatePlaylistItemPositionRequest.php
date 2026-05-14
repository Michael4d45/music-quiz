<?php

declare(strict_types=1);

namespace App\Features\Playlists\Requests;

use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class UpdatePlaylistItemPositionRequest extends Data
{
    public function __construct(
        #[Nullable, Uuid]
        public ?string $before_item_id = null,
    ) {}
}
