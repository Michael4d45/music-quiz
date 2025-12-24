<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks\Pages;

use App\Filament\Resources\TrackSourceLinks\TrackSourceLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrackSourceLink extends EditRecord
{
    protected static string $resource = TrackSourceLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
