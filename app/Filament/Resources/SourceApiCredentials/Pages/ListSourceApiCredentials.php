<?php

declare(strict_types=1);

namespace App\Filament\Resources\SourceApiCredentials\Pages;

use App\Filament\Resources\SourceApiCredentials\SourceApiCredentialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSourceApiCredentials extends ListRecords
{
    protected static string $resource = SourceApiCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
