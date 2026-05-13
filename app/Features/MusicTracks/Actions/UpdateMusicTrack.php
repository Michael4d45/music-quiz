<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Data\Models\MusicTrackData;
use App\Features\MusicTracks\Requests\UpdateMusicTrackRequest;
use App\Models\MusicTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdateMusicTrack
{
    public function __invoke(Request $request, MusicTrack $musicTrack): Response
    {
        Gate::authorize('update', $musicTrack);

        $validatedResult = UpdateMusicTrackRequest::validate($request->only([
            'title',
            'artist_name',
            'album_name',
            'release_year',
            'genre',
            'duration_ms',
            'sub_category_id',
            'primary_source_id',
            'origin_kind',
            'origin_title',
        ]));
        $validated = is_array($validatedResult)
            ? $validatedResult
            : $validatedResult->toArray();
        $musicTrack->fill($validated);
        $musicTrack->save();
        $musicTrack->load(['subCategory', 'primarySource']);

        return response()->json(MusicTrackData::from($musicTrack));
    }
}
