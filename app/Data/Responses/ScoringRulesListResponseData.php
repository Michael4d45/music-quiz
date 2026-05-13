<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\ScoringRuleData;
use Spatie\LaravelData\Data;

class ScoringRulesListResponseData extends Data
{
    /**
     * @param list<ScoringRuleData> $scoring_rules
     */
    public function __construct(
        public array $scoring_rules,
    ) {}
}
