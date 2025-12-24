<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackAvailabilities\Pages;

use App\Filament\Resources\TrackAvailabilities\TrackAvailabilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrackAvailability extends EditRecord
{
    protected static string $resource = TrackAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
