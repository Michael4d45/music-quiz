<?php

declare(strict_types=1);

namespace App\Features\Playlists\Requests;

use App\Enums\PlaylistStatus;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePlaylistRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', new Enum(PlaylistStatus::class)],
            'visibility' => ['sometimes', new Enum(Visibility::class)],
        ];
    }
}
