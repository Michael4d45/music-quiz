<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Actions;

use App\Data\Models\QuizQuestionData;
use App\Features\QuizQuestions\Requests\UpdateQuizQuestionRequest;
use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdateQuizQuestion
{
    public function __invoke(
        UpdateQuizQuestionRequest $request,
        QuizQuestion $quizQuestion,
    ): Response {
        Gate::authorize('update', $quizQuestion);

        $validated = $request->validated();

        if (array_key_exists('track_id', $validated) && $validated['track_id'] !== null) {
            $track = MusicTrack::query()->findOrFail($validated['track_id']);
            Gate::authorize('view', $track);
        }

        $quizQuestion->fill($validated);
        $quizQuestion->save();
        $quizQuestion->load('track');

        return response()->json(QuizQuestionData::from($quizQuestion));
    }
}
