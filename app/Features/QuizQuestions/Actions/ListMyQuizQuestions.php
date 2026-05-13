<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Actions;

use App\Data\Models\QuizQuestionData;
use App\Data\Responses\MyQuizQuestionsResponseData;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ListMyQuizQuestions
{
    public function __invoke(): Response
    {
        $user = assertedUser();
        Gate::authorize('viewAny', QuizQuestion::class);

        $questions = QuizQuestion::query()
            ->where('user_id', $user->id)
            ->with(['track'])
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get();

        $mapped = $questions
            ->map(static fn(QuizQuestion $q): QuizQuestionData => QuizQuestionData::from($q))
            ->all();

        return response()->json(MyQuizQuestionsResponseData::from([
            'questions' => $mapped,
        ]));
    }
}
