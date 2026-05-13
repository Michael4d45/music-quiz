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
        $validated = $request->validated();

        $track = MusicTrack::query()->create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'artist_name' => $validated['artist_name'],
            'album_name' => $validated['album_name'] ?? null,
            'release_year' => $validated['release_year'] ?? null,
            'genre' => $validated['genre'] ?? null,
            'duration_ms' => $validated['duration_ms'] ?? null,
            'sub_category_id' => $validated['sub_category_id'],
            'primary_source_id' => $validated['primary_source_id'],
        ]);

        $track->load(['subCategory', 'primarySource']);

        return response()->json(MusicTrackData::from($track), 201);
    }
}
