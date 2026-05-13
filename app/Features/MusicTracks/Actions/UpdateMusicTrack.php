<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Data\Models\MusicTrackData;
use App\Features\MusicTracks\Requests\UpdateMusicTrackRequest;
use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdateMusicTrack
{
    public function __invoke(
        UpdateMusicTrackRequest $request,
        MusicTrack $musicTrack,
    ): Response {
        Gate::authorize('update', $musicTrack);

        $musicTrack->fill($request->validated());
        $musicTrack->save();
        $musicTrack->load(['subCategory', 'primarySource']);

        return response()->json(MusicTrackData::from($musicTrack));
    }
}
