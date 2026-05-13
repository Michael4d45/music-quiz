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
        $validated = $request->validated();

        if (array_key_exists('track_id', $validated) && $validated['track_id'] !== null) {
            $track = MusicTrack::query()->findOrFail($validated['track_id']);
            Gate::authorize('view', $track);
        }

        $question = QuizQuestion::query()->create([
            'user_id' => $user->id,
            'track_id' => $validated['track_id'] ?? null,
            'question_type' => $validated['question_type'],
            'prompt_text' => $validated['prompt_text'] ?? null,
            'correct_answer' => $validated['correct_answer'],
            'base_points' => $validated['base_points'],
            'media_start_seconds' => $validated['media_start_seconds'] ?? null,
            'media_end_seconds' => $validated['media_end_seconds'] ?? null,
            'difficulty_level' => $validated['difficulty_level'],
            'visibility' => $validated['visibility'] ?? Visibility::Private,
        ]);

        $question->load('track');

        return response()->json(QuizQuestionData::from($question), 201);
    }
}
