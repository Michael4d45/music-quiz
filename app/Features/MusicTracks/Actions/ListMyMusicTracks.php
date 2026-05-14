<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Data\Models\MusicTrackData;
use App\Data\Responses\MyMusicTracksResponseData;
use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ListMyMusicTracks
{
    public function __invoke(): Response
    {
        $user = assertedUser();
        Gate::authorize('viewAny', MusicTrack::class);

        $tracks = MusicTrack::query()
            ->where('user_id', $user->id)
            ->with(['subCategory', 'primarySource'])
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get();

        return response()->json(MyMusicTracksResponseData::from([
            'tracks' => MusicTrackData::collect($tracks),
        ]));
    }
}
