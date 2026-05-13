<?php

declare(strict_types=1);

namespace App\Features\Reference\Actions;

use App\Data\Models\ScoringRuleData;
use App\Data\Responses\ScoringRulesListResponseData;
use App\Models\ScoringRule;
use Symfony\Component\HttpFoundation\Response;

class ListScoringRules
{
    public function __invoke(): Response
    {
        $rules = ScoringRule::query()->orderBy('name')->get();

        $mapped = $rules->map(
            static fn(ScoringRule $rule): ScoringRuleData => ScoringRuleData::from(
                $rule,
            ),
        )->all();

        return response()->json(ScoringRulesListResponseData::from([
            'scoring_rules' => $mapped,
        ]));
    }
}
