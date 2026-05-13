<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Actions;

use App\Data\Models\QuizQuestionData;
use App\Enums\Visibility;
use App\Features\QuizQuestions\Requests\StoreQuizQuestionRequest;
use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CreateQuizQuestion
{
    public function __invoke(StoreQuizQuestionRequest $request): Response
    {
        Gate::authorize('create', QuizQuestion::class);

        $user = assertedUser();

        if ($request->track_id !== null) {
            $track = MusicTrack::query()->findOrFail($request->track_id);
            Gate::authorize('view', $track);
        }

        $question = QuizQuestion::query()->create([
            'user_id' => $user->id,
            'track_id' => $request->track_id,
            'question_type' => $request->question_type,
            'prompt_text' => $request->prompt_text,
            'correct_answer' => $request->correct_answer,
            'base_points' => $request->base_points,
            'media_start_seconds' => $request->media_start_seconds,
            'media_end_seconds' => $request->media_end_seconds,
            'difficulty_level' => $request->difficulty_level,
            'visibility' => $request->visibility ?? Visibility::Private,
        ]);

        $question->load('track');

        return response()->json(QuizQuestionData::from($question), 201);
    }
}
