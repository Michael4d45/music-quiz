<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics\Pages;

use App\Filament\Resources\UserStatistics\UserStatisticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserStatistics extends ListRecords
{
    protected static string $resource = UserStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
