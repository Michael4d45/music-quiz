<?php

declare(strict_types=1);

namespace App\Data\Response;

use Spatie\LaravelData\Data;

class SubmitAnswerResponse extends Data
{
    public function __construct(
        public bool $is_correct,
        public int $points_awarded,
        public string $correct_answer,
    ) {}
}
