<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Data\Models\PlaylistData;
use App\Features\Playlists\Requests\UpdatePlaylistRequest;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdatePlaylist
{
    public function __invoke(Request $request, Playlist $playlist): Response
    {
        Gate::authorize('update', $playlist);

        $keys = collect(['name', 'description', 'visibility'])
            ->filter(fn(string $key): bool => $request->has($key))
            ->values()
            ->all();
        $validatedResult = UpdatePlaylistRequest::validate($request->only(
            $keys,
        ));
        $validated = is_array($validatedResult)
            ? $validatedResult
            : $validatedResult->toArray();
        $playlist->fill($validated);
        $playlist->save();

        return response()->json(PlaylistData::from($playlist));
    }
}
