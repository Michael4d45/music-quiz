<?php

declare(strict_types=1);

namespace App\Features\QuizQuestions\Requests;

use App\Enums\QuestionType;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreQuizQuestionRequest extends FormRequest
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
            'track_id' => ['nullable', 'uuid', 'exists:music_tracks,id'],
            'question_type' => ['required', new Enum(QuestionType::class)],
            'prompt_text' => ['nullable', 'string'],
            'correct_answer' => ['required', 'string', 'max:2000'],
            'base_points' => ['required', 'integer', 'min:0', 'max:100000'],
            'media_start_seconds' => ['nullable', 'integer', 'min:0'],
            'media_end_seconds' => ['nullable', 'integer', 'min:0'],
            'difficulty_level' => ['required', 'integer', 'min:1', 'max:10'],
            'visibility' => ['sometimes', new Enum(Visibility::class)],
        ];
    }
}
