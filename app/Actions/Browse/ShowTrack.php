<?php

declare(strict_types=1);

namespace App\Actions\Browse;

use App\Data\Models\MusicTrackData;
use App\Data\Response\TrackResponse;
use App\Models\MusicTrack;
use Illuminate\Http\JsonResponse;

class ShowTrack
{
    /**
     * Display the track detail data.
     */
    public function __invoke(MusicTrack $track): JsonResponse
    {
        $track->load([
            'quizQuestions',
            'sourceLinks.source',
            'subCategory',
            'primarySource',
        ]);

        return response()->json(TrackResponse::from([
            'track' => MusicTrackData::from($track),
        ]));
    }
}
