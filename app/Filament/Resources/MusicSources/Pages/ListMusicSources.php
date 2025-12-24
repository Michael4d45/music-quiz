<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\Pages;

use App\Filament\Resources\MusicSources\MusicSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMusicSources extends ListRecords
{
    protected static string $resource = MusicSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
