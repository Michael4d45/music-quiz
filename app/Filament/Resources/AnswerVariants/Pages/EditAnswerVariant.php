<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Pages;

use App\Filament\Resources\AnswerVariants\AnswerVariantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnswerVariant extends EditRecord
{
    protected static string $resource = AnswerVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
