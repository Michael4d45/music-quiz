<?php

declare(strict_types=1);

namespace App\Actions\Playlists;

use App\Data\Models\PlaylistData;
use App\Data\Response\PlaylistsResponse;
use App\Http\Requests\AuthRequest;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;

class ShowPlaylists
{
    /**
     * Display the user's playlists.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $playlists = Playlist::where('user_id', $user->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(PlaylistsResponse::from([
            'playlists' => PlaylistData::collect($playlists),
        ]));
    }
}
