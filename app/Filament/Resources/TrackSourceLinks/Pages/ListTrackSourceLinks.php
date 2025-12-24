<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks\Pages;

use App\Filament\Resources\TrackSourceLinks\TrackSourceLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrackSourceLinks extends ListRecords
{
    protected static string $resource = TrackSourceLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
