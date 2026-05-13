<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\PlaylistData;
use Spatie\LaravelData\Data;

class MyPlaylistsResponseData extends Data
{
    /**
     * @param list<PlaylistData> $playlists
     */
    public function __construct(
        public array $playlists,
    ) {}
}
