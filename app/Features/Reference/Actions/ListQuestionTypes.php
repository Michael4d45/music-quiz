<?php

declare(strict_types=1);

namespace App\Features\Reference\Actions;

use App\Data\Models\IdLabelOptionData;
use App\Data\Responses\QuestionTypesListResponseData;
use App\Enums\QuestionType;
use Symfony\Component\HttpFoundation\Response;

class ListQuestionTypes
{
    public function __invoke(): Response
    {
        return response()->json(QuestionTypesListResponseData::from([
            'question_types' => IdLabelOptionData::collect(array_map(
                static fn(QuestionType $type): array => [
                    'id' => $type->value,
                    'label' => $type->getLabel(),
                ],
                QuestionType::cases(),
            )),
        ]));
    }
}
