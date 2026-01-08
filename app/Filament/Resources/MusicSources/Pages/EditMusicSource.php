<?php

declare(strict_types=1);

namespace App\Filament\Resources\MusicSources\Pages;

use App\Filament\Resources\MusicSources\MusicSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMusicSource extends EditRecord
{
    protected static string $resource = MusicSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
