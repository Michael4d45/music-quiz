<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSessionRoundAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'submitted_text' => ['nullable', 'string', 'max:2000'],
            'selected_option_id' => ['nullable', 'uuid'],
        ];
    }
}
