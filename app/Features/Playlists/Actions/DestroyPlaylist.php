<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Models\Playlist;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DestroyPlaylist
{
    public function __invoke(Playlist $playlist): Response
    {
        Gate::authorize('delete', $playlist);

        $playlist->delete();

        return response()->json(['message' => 'Playlist deleted']);
    }
}
