<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnswerVariants\Pages;

use App\Filament\Resources\AnswerVariants\AnswerVariantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnswerVariant extends CreateRecord
{
    protected static string $resource = AnswerVariantResource::class;
}
