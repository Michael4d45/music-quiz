<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserStatistics\Pages;

use App\Filament\Resources\UserStatistics\UserStatisticResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserStatistic extends EditRecord
{
    protected static string $resource = UserStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
