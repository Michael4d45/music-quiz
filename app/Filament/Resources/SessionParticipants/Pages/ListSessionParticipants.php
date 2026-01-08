<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionParticipants\Pages;

use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSessionParticipants extends ListRecords
{
    protected static string $resource = SessionParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
