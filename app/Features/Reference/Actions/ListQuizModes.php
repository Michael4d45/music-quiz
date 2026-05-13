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

        $mapped = $modes->map(
            static fn(QuizMode $mode): QuizModeData => QuizModeData::from(
                $mode,
            ),
        )->all();

        return response()->json(QuizModesListResponseData::from([
            'quiz_modes' => $mapped,
        ]));
    }
}
