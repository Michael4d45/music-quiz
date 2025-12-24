<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\Pages;

use App\Filament\Resources\MusicSources\MusicSourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMusicSource extends CreateRecord
{
    protected static string $resource = MusicSourceResource::class;
}
