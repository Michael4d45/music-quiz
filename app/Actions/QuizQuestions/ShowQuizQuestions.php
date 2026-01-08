<?php

declare(strict_types=1);

namespace App\Actions\QuizQuestions;

use App\Data\Models\QuizQuestionData;
use App\Data\Response\QuizQuestionsResponse;
use App\Http\Requests\AuthRequest;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowQuizQuestions
{
    /**
     * Display the user's quiz questions.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $quizQuestions = QuizQuestion::where('user_id', $user->id)
            ->with(['track'])
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return response()->json(QuizQuestionsResponse::from([
            'quiz_questions' => QuizQuestionData::collect(
                $quizQuestions,
                LengthAwarePaginator::class,
            ),
        ]));
    }
}
