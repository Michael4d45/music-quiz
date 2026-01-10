<?php

declare(strict_types=1);

namespace App\Actions\MusicSources;

use App\Data\Models\MusicSourceData;
use App\Data\Response\MusicSourcesResponse;
use App\Models\MusicSource;
use Illuminate\Http\JsonResponse;

class ShowMusicSources
{
    /**
     * Display all music sources.
     */
    public function __invoke(): JsonResponse
    {
        $musicSources = MusicSource::orderBy('name')->get();

        return response()->json(MusicSourcesResponse::from([
            'music_sources' => MusicSourceData::collect($musicSources),
        ]));
    }
}