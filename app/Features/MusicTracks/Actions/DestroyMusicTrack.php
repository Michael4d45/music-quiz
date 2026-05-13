<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DestroyMusicTrack
{
    public function __invoke(MusicTrack $musicTrack): Response
    {
        Gate::authorize('delete', $musicTrack);

        $musicTrack->delete();

        return response()->json(['message' => 'Track deleted']);
    }
}
