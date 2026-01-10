<?php

declare(strict_types=1);

namespace App\Actions\MusicTracks;

use App\Data\Models\MusicTrackData;
use App\Data\Response\UserMusicTracksResponse;
use App\Http\Requests\AuthRequest;
use App\Models\MusicTrack;
use Illuminate\Http\JsonResponse;

class ShowUserMusicTracks
{
    /**
     * Display the user's music tracks for quiz question creation.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $tracks = MusicTrack::where('user_id', $user->id)
            ->with(['subCategory', 'primarySource'])
            ->orderBy('title')
            ->get();

        return response()->json(UserMusicTracksResponse::from([
            'tracks' => MusicTrackData::collect($tracks),
        ]));
    }
}