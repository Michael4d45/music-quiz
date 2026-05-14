<?php

declare(strict_types=1);

namespace App\Features\Reference\Actions;

use App\Data\Models\QuizModeData;
use App\Data\Responses\QuizModesListResponseData;
use App\Models\QuizMode;
use Symfony\Component\HttpFoundation\Response;

class ListQuizModes
{
    public function __invoke(): Response
    {
        $modes = QuizMode::query()->orderBy('name')->get();

        return response()->json(QuizModesListResponseData::from([
            'quiz_modes' => QuizModeData::collect($modes),
        ]));
    }
}
