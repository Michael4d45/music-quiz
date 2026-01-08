<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionParticipants\Pages;

use App\Filament\Resources\SessionParticipants\SessionParticipantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSessionParticipant extends CreateRecord
{
    protected static string $resource = SessionParticipantResource::class;
}
