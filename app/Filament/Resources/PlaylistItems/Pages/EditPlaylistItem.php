<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlaylistItems\Pages;

use App\Filament\Resources\PlaylistItems\PlaylistItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlaylistItem extends EditRecord
{
    protected static string $resource = PlaylistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
