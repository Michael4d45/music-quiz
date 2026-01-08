<?php

declare(strict_types=1);

namespace App\Actions\QuizQuestions;

use App\Data\Models\QuizQuestionData;
use App\Data\Requests\CreateQuizQuestionRequest;
use App\Data\Response\QuizQuestionResponse;
use App\Http\Requests\AuthRequest;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;

class CreateQuizQuestion
{
    /**
     * Handle the incoming request to create a quiz question.
     */
    public function __invoke(
        CreateQuizQuestionRequest $data,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $question = QuizQuestion::create([
            'user_id' => $user->id,
            'track_id' => $data->track_id,
            'question_type' => $data->question_type,
            'prompt_text' => $data->prompt_text,
            'correct_answer' => $data->correct_answer,
            'base_points' => $data->base_points,
            'media_start_seconds' => $data->media_start_seconds,
            'media_end_seconds' => $data->media_end_seconds,
            'difficulty_level' => $data->difficulty_level,
            'visibility' => $data->visibility,
        ]);

        $question->load(['track']);

        return response()->json(QuizQuestionResponse::from([
            'quiz_question' => QuizQuestionData::from($question),
        ]), 201);
    }
}
