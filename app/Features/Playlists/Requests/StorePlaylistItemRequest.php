<?php

declare(strict_types=1);

namespace App\Features\Playlists\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlaylistItemRequest extends FormRequest
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
            'question_id' => [
                'required',
                'uuid',
                Rule::exists('quiz_questions', 'id'),
            ],
        ];
    }
}
