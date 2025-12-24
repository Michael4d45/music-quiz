<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizModes\Pages;

use App\Filament\Resources\QuizModes\QuizModeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuizModes extends ListRecords
{
    protected static string $resource = QuizModeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
