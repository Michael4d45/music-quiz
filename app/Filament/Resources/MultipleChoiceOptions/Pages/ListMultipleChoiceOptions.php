<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\Pages;

use App\Filament\Resources\MultipleChoiceOptions\MultipleChoiceOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMultipleChoiceOptions extends ListRecords
{
    protected static string $resource = MultipleChoiceOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
