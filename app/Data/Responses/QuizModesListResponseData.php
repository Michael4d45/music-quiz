<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\QuizModeData;
use Spatie\LaravelData\Data;

class QuizModesListResponseData extends Data
{
    /**
     * @param list<QuizModeData> $quiz_modes
     */
    public function __construct(
        public array $quiz_modes,
    ) {}
}
