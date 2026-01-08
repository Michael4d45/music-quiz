<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackAvailabilities\Pages;

use App\Filament\Resources\TrackAvailabilities\TrackAvailabilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrackAvailabilities extends ListRecords
{
    protected static string $resource = TrackAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
