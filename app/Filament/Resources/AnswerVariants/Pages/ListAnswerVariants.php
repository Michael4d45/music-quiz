<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Pages;

use App\Filament\Resources\AnswerVariants\AnswerVariantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnswerVariants extends ListRecords
{
    protected static string $resource = AnswerVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
