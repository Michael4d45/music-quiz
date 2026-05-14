<?php

declare(strict_types=1);

namespace App\Data\Responses;

use Spatie\LaravelData\Data;

class AnswerVariantGameplayData extends Data
{
    public function __construct(
        public string $id,
        public null|string $accepted_text,
    ) {}
}
