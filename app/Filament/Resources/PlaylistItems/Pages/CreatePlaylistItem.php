<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems\Pages;

use App\Filament\Resources\PlaylistItems\PlaylistItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlaylistItem extends CreateRecord
{
    protected static string $resource = PlaylistItemResource::class;
}
