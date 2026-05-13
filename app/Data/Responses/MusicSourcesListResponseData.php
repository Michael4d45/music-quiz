<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\IdLabelOptionData;
use Spatie\LaravelData\Data;

class MusicSourcesListResponseData extends Data
{
    /**
     * @param list<IdLabelOptionData> $music_sources
     */
    public function __construct(
        public array $music_sources,
    ) {}
}
