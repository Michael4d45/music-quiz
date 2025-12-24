<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Pages;

use App\Filament\Resources\SessionFinalScores\SessionFinalScoreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSessionFinalScores extends ListRecords
{
    protected static string $resource = SessionFinalScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
