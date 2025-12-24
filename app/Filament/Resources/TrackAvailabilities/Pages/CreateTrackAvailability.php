<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackAvailabilities\Pages;

use App\Filament\Resources\TrackAvailabilities\TrackAvailabilityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrackAvailability extends CreateRecord
{
    protected static string $resource = TrackAvailabilityResource::class;
}
