<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Actions;

use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DestroyQuizQuestion
{
    public function __invoke(QuizQuestion $quizQuestion): Response
    {
        Gate::authorize('delete', $quizQuestion);

        $quizQuestion->delete();

        return response()->json(['message' => 'Question deleted']);
    }
}
