<?php

declare(strict_types=1);

namespace App\Filament\Resources\SessionFinalScores\Pages;

use App\Filament\Resources\SessionFinalScores\SessionFinalScoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSessionFinalScore extends CreateRecord
{
    protected static string $resource = SessionFinalScoreResource::class;
}
