<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\IdLabelOptionData;
use Spatie\LaravelData\Data;

class QuestionTypesListResponseData extends Data
{
    /**
     * @param list<IdLabelOptionData> $question_types
     */
    public function __construct(
        public array $question_types,
    ) {}
}
