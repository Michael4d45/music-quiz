<?php

declare(strict_types=1);

namespace App\Actions\MusicTracks;

use App\Data\Models\MusicTrackData;
use App\Data\Requests\CreateMusicTrackRequest;
use App\Data\Response\MusicTrackResponse;
use App\Http\Requests\AuthRequest;
use App\Models\MusicTrack;
use Illuminate\Http\JsonResponse;

class CreateMusicTrack
{
    /**
     * Handle the incoming request to create a music track.
     */
    public function __invoke(
        CreateMusicTrackRequest $data,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $track = MusicTrack::create([
            'user_id' => $user->id,
            'title' => $data->title,
            'artist_name' => $data->artist_name,
            'album_name' => $data->album_name,
            'release_year' => $data->release_year,
            'genre' => $data->genre,
            'duration_ms' => $data->duration_ms,
            'sub_category_id' => $data->sub_category_id,
            'primary_source_id' => $data->primary_source_id,
        ]);

        $track->load(['subCategory', 'primarySource']);

        return response()->json(MusicTrackResponse::from([
            'music_track' => MusicTrackData::from($track),
        ]), 201);
    }
}
