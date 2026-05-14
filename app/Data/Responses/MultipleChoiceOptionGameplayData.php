<?php

declare(strict_types=1);

namespace App\Data\Responses;

use Spatie\LaravelData\Data;

class MultipleChoiceOptionGameplayData extends Data
{
    public function __construct(
        public string $id,
        public string $question_id,
        public string $option_text,
        public null|int $sort_order,
        public null|bool $is_correct,
    ) {}
}
