<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\PlaylistItemData;
use Spatie\LaravelData\Data;

class MyPlaylistItemsResponseData extends Data
{
    /**
     * @param list<PlaylistItemData> $items
     */
    public function __construct(
        public array $items,
    ) {}
}
