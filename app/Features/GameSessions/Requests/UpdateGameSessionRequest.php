<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|\Illuminate\Validation\Rules\Exists>>
     */
    public function rules(): array
    {
        return [
            'is_public' => ['sometimes', 'boolean'],
            'max_players' => ['sometimes', 'integer', 'min:2', 'max:50'],
            'playlist_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('playlists', 'id')],
        ];
    }
}
