<?php

declare(strict_types=1);

namespace App\Features\Reference\Actions;

use App\Data\Models\IdLabelOptionData;
use App\Data\Responses\MusicSourcesListResponseData;
use App\Models\MusicSource;
use Symfony\Component\HttpFoundation\Response;

class ListMusicSources
{
    public function __invoke(): Response
    {
        $sources = MusicSource::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('display_name')
            ->get();

        $mapped = $sources->map(static fn(MusicSource $source): IdLabelOptionData => IdLabelOptionData::from([
            'id' => $source->id,
            'label' => $source->display_name,
        ]))->all();

        return response()->json(MusicSourcesListResponseData::from([
            'music_sources' => $mapped,
        ]));
    }
}
