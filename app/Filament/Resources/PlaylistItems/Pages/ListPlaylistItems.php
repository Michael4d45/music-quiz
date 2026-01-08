<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems\Pages;

use App\Filament\Resources\PlaylistItems\PlaylistItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlaylistItems extends ListRecords
{
    protected static string $resource = PlaylistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
