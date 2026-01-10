<?php

declare(strict_types=1);

namespace App\Actions\Playlists;

use App\Data\Models\PlaylistData;
use App\Data\Response\UserPlaylistsResponse;
use App\Http\Requests\AuthRequest;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;

class ShowUserPlaylists
{
    /**
     * Display the user's playlists for session creation.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $playlists = Playlist::where('user_id', $user->id)
            ->with(['user', 'items.question'])
            ->orderBy('name')
            ->get();

        return response()->json(UserPlaylistsResponse::from([
            'playlists' => PlaylistData::collect($playlists),
        ]));
    }
}