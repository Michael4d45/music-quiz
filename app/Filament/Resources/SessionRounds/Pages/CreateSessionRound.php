<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds\Pages;

use App\Filament\Resources\SessionRounds\SessionRoundResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSessionRound extends CreateRecord
{
    protected static string $resource = SessionRoundResource::class;
}
