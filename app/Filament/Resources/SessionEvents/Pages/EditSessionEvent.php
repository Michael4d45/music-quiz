<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Pages;

use App\Filament\Resources\SessionEvents\SessionEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSessionEvent extends EditRecord
{
    protected static string $resource = SessionEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
