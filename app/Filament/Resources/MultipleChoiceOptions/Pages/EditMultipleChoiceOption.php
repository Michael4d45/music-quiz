<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\Pages;

use App\Filament\Resources\MultipleChoiceOptions\MultipleChoiceOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMultipleChoiceOption extends EditRecord
{
    protected static string $resource = MultipleChoiceOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
