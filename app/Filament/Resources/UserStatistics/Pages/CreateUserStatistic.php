<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics\Pages;

use App\Filament\Resources\UserStatistics\UserStatisticResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserStatistic extends CreateRecord
{
    protected static string $resource = UserStatisticResource::class;
}
