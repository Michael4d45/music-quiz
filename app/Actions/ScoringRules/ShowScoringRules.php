<?php

declare(strict_types=1);

namespace App\Actions\ScoringRules;

use App\Data\Models\ScoringRuleData;
use App\Data\Response\ScoringRulesResponse;
use App\Models\ScoringRule;
use Illuminate\Http\JsonResponse;

class ShowScoringRules
{
    /**
     * Display all available scoring rules.
     */
    public function __invoke(): JsonResponse
    {
        $scoringRules = ScoringRule::orderBy('name')->get();

        return response()->json(ScoringRulesResponse::from([
            'scoring_rules' => ScoringRuleData::collect($scoringRules),
        ]));
    }
}