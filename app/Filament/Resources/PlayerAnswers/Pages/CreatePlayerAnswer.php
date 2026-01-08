<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlayerAnswers\Pages;

use App\Filament\Resources\PlayerAnswers\PlayerAnswerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlayerAnswer extends CreateRecord
{
    protected static string $resource = PlayerAnswerResource::class;
}
