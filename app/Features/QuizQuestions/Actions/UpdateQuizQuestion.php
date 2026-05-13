<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Actions;

use App\Data\Models\QuizQuestionData;
use App\Features\QuizQuestions\Requests\UpdateQuizQuestionRequest;
use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdateQuizQuestion
{
    public function __invoke(
        Request $request,
        QuizQuestion $quizQuestion,
    ): Response {
        Gate::authorize('update', $quizQuestion);

        $validatedResult = UpdateQuizQuestionRequest::validate($request->only([
            'track_id',
            'question_type',
            'prompt_text',
            'correct_answer',
            'base_points',
            'media_start_seconds',
            'media_end_seconds',
            'difficulty_level',
            'visibility',
        ]));
        $validated = is_array($validatedResult)
            ? $validatedResult
            : $validatedResult->toArray();

        if (
            array_key_exists('track_id', $validated)
            && $validated['track_id'] !== null
        ) {
            $track = MusicTrack::query()->findOrFail($validated['track_id']);
            Gate::authorize('view', $track);
        }

        $quizQuestion->fill($validated);
        $quizQuestion->save();
        $quizQuestion->load('track');

        return response()->json(QuizQuestionData::from($quizQuestion));
    }
}
