<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionParticipants\Pages;

use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSessionParticipant extends EditRecord
{
    protected static string $resource = SessionParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
