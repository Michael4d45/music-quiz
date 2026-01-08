<?php

declare(strict_types=1);

namespace App\Filament\Resources\ScoringRules\Pages;

use App\Filament\Resources\ScoringRules\ScoringRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScoringRule extends CreateRecord
{
    protected static string $resource = ScoringRuleResource::class;
}
