<?php

declare(strict_types=1);

namespace App\Actions\QuizModes;

use App\Data\Models\QuizModeData;
use App\Data\Response\QuizModesResponse;
use App\Models\QuizMode;
use Illuminate\Http\JsonResponse;

class ShowQuizModes
{
    /**
     * Display all available quiz modes.
     */
    public function __invoke(): JsonResponse
    {
        $quizModes = QuizMode::orderBy('name')->get();

        return response()->json(QuizModesResponse::from([
            'quiz_modes' => QuizModeData::collect($quizModes),
        ]));
    }
}
