<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuizModes\Pages;

use App\Filament\Resources\QuizModes\QuizModeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuizMode extends CreateRecord
{
    protected static string $resource = QuizModeResource::class;
}
