<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Requests;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'track_id' => ['sometimes', 'nullable', 'uuid', 'exists:music_tracks,id'],
            'question_type' => ['sometimes', new Enum(QuestionType::class)],
            'prompt_text' => ['sometimes', 'nullable', 'string'],
            'correct_answer' => ['sometimes', 'string', 'max:2000'],
            'base_points' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'media_start_seconds' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'media_end_seconds' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'difficulty_level' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'visibility' => ['sometimes', new Enum(Visibility::class)],
        ];
    }
}
