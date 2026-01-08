<?php

declare(strict_types=1);

namespace App\Filament\Resources\MultipleChoiceOptions\Pages;

use App\Filament\Resources\MultipleChoiceOptions\MultipleChoiceOptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMultipleChoiceOption extends CreateRecord
{
    protected static string $resource = MultipleChoiceOptionResource::class;
}
