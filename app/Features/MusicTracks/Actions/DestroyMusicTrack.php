<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Features\Auth\Responses\MessageResponse;
use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DestroyMusicTrack
{
    public function __invoke(MusicTrack $musicTrack): Response
    {
        Gate::authorize('delete', $musicTrack);

        if ($musicTrack->user_upload_path !== null) {
            Storage::disk('local')->delete($musicTrack->user_upload_path);
        }

        $musicTrack->delete();

        return response()->json(MessageResponse::from([
            'message' => 'Track deleted',
        ]));
    }
}
