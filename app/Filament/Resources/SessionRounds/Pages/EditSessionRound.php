<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionRounds\Pages;

use App\Filament\Resources\SessionRounds\SessionRoundResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSessionRound extends EditRecord
{
    protected static string $resource = SessionRoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
