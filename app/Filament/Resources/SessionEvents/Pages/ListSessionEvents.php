<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionEvents\Pages;

use App\Filament\Resources\SessionEvents\SessionEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSessionEvents extends ListRecords
{
    protected static string $resource = SessionEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
