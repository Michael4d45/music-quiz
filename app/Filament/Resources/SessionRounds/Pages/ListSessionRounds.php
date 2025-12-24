<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds\Pages;

use App\Filament\Resources\SessionRounds\SessionRoundResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSessionRounds extends ListRecords
{
    protected static string $resource = SessionRoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
