<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackSourceLinks\Pages;

use App\Filament\Resources\TrackSourceLinks\TrackSourceLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrackSourceLink extends CreateRecord
{
    protected static string $resource = TrackSourceLinkResource::class;
}
