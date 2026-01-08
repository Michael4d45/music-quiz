<?php

declare(strict_types=1);

namespace App\Actions\Playlists;

use App\Data\Models\PlaylistData;
use App\Data\Response\PlaylistResponse;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;

class ShowPlaylist
{
    /**
     * Display the playlist detail.
     */
    public function __invoke(Playlist $playlist): JsonResponse
    {
        $playlist->load(['user', 'items.question']);

        return response()->json(PlaylistResponse::from([
            'playlist' => PlaylistData::from($playlist),
        ]));
    }
}
