<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Data\Models\MusicTrackData;
use App\Features\MusicTracks\Requests\StoreMusicTrackRequest;
use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CreateMusicTrack
{
    public function __invoke(StoreMusicTrackRequest $request): Response
    {
        Gate::authorize('create', MusicTrack::class);

        $user = assertedUser();

        $track = MusicTrack::query()->create([
            'user_id' => $user->id,
            'title' => $request->title,
            'artist_name' => $request->artist_name,
            'album_name' => $request->album_name,
            'release_year' => $request->release_year,
            'genre' => $request->genre,
            'duration_ms' => $request->duration_ms,
            'origin_kind' => $request->origin_kind,
            'origin_title' => $request->origin_title,
            'sub_category_id' => $request->sub_category_id,
            'primary_source_id' => $request->primary_source_id,
        ]);

        $track->load(['subCategory', 'primarySource']);

        return response()->json(MusicTrackData::from($track), 201);
    }
}
