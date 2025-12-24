<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Pages;

use App\Filament\Resources\SessionFinalScores\SessionFinalScoreResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSessionFinalScore extends EditRecord
{
    protected static string $resource = SessionFinalScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
