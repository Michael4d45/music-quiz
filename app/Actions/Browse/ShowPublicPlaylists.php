<?php

declare(strict_types=1);

namespace App\Actions\Browse;

use App\Data\Models\PlaylistData;
use App\Data\Response\PlaylistsResponse;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowPublicPlaylists
{
    /**
     * Display the public playlists data.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $playlists = Playlist::where('is_public', true)
            ->with('user')
            ->orderBy('play_count', 'desc')
            ->paginate(24);

        return response()->json(PlaylistsResponse::from([
            'playlists' => PlaylistData::collect($playlists),
        ]));
    }
}
