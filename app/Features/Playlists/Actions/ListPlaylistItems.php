<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Data\Models\PlaylistItemData;
use App\Data\Responses\MyPlaylistItemsResponseData;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ListPlaylistItems
{
    public function __invoke(Playlist $playlist): Response
    {
        Gate::authorize('view', $playlist);

        $items = PlaylistItem::query()
            ->where('playlist_id', $playlist->id)
            ->with(['question'])
            ->orderBy('sort_order')
            ->get();

        $mapped = $items->map(
            static fn(PlaylistItem $item): PlaylistItemData => PlaylistItemData::from(
                $item,
            ),
        )->all();

        return response()->json(MyPlaylistItemsResponseData::from([
            'items' => $mapped,
        ]));
    }
}
