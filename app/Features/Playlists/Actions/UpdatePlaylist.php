<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Data\Models\PlaylistData;
use App\Features\Playlists\Requests\UpdatePlaylistRequest;
use App\Models\Playlist;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdatePlaylist
{
    public function __invoke(
        UpdatePlaylistRequest $request,
        Playlist $playlist,
    ): Response {
        Gate::authorize('update', $playlist);

        $playlist->fill($request->validated());
        $playlist->save();

        return response()->json(PlaylistData::from($playlist));
    }
}
