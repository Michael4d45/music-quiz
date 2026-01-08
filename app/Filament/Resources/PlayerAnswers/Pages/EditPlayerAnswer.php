<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Pages;

use App\Filament\Resources\PlayerAnswers\PlayerAnswerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayerAnswer extends EditRecord
{
    protected static string $resource = PlayerAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
