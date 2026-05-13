<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Data\Models\PlaylistData;
use App\Data\Responses\MyPlaylistsResponseData;
use App\Models\Playlist;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ListMyPlaylists
{
    public function __invoke(): Response
    {
        $user = assertedUser();
        Gate::authorize('viewAny', Playlist::class);

        $playlists = Playlist::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        $mapped = $playlists->map(
            static fn(Playlist $playlist): PlaylistData => PlaylistData::from(
                $playlist,
            ),
        )->all();

        return response()->json(MyPlaylistsResponseData::from([
            'playlists' => $mapped,
        ]));
    }
}
