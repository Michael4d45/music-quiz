<?php

declare(strict_types=1);

namespace App\Actions\Browse;

use App\Data\Models\MusicTrackData;
use App\Data\Response\TracksResponse;
use App\Models\MusicTrack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowTracks
{
    /**
     * Display the tracks browser data.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = MusicTrack::with(['subCategory', 'primarySource']);

        $search = $request->query('search');
        if (is_string($search) && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere(
                    'artist_name',
                    'like',
                    "%{$search}%",
                );
            });
        }

        $tracks = $query->orderBy('created_at', 'desc')->paginate(24);

        return response()->json(TracksResponse::from([
            'tracks' => MusicTrackData::collect(
                $tracks,
                LengthAwarePaginator::class,
            ),
        ]));
    }
}
