<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\MusicTrackData;
use Spatie\LaravelData\Data;

class MyMusicTracksResponseData extends Data
{
    /**
     * @param list<MusicTrackData> $tracks
     */
    public function __construct(
        public array $tracks,
    ) {}
}
