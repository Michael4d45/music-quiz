<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Pages;

use App\Filament\Resources\PlayerAnswers\PlayerAnswerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlayerAnswers extends ListRecords
{
    protected static string $resource = PlayerAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
