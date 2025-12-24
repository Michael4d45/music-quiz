<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizModes\Pages;

use App\Filament\Resources\QuizModes\QuizModeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuizMode extends EditRecord
{
    protected static string $resource = QuizModeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
