<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RemovePlaylistItem
{
    public function __invoke(Playlist $playlist, PlaylistItem $playlistItem): Response
    {
        Gate::authorize('update', $playlist);

        if ($playlistItem->playlist_id !== $playlist->id) {
            abort(404);
        }

        $playlistItem->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
