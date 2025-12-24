<?php

declare(strict_types=1);

namespace App\Filament\Resources\ScoringRules\Pages;

use App\Filament\Resources\ScoringRules\ScoringRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScoringRules extends ListRecords
{
    protected static string $resource = ScoringRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
