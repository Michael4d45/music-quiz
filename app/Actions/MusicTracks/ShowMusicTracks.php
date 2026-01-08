<?php

declare(strict_types=1);

namespace App\Actions\MusicTracks;

use App\Data\Models\MusicTrackData;
use App\Data\Response\MusicTracksResponse;
use App\Http\Requests\AuthRequest;
use App\Models\MusicTrack;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowMusicTracks
{
    /**
     * Display the user's music tracks.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $musicTracks = MusicTrack::where('user_id', $user->id)
            ->with(['subCategory', 'primarySource'])
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return response()->json(MusicTracksResponse::from([
            'music_tracks' => MusicTrackData::collect(
                $musicTracks,
                LengthAwarePaginator::class,
            ),
        ]));
    }
}
