<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMusicTrackRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'artist_name' => ['sometimes', 'string', 'max:255'],
            'album_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'release_year' => ['sometimes', 'nullable', 'integer', 'min:1800', 'max:2100'],
            'genre' => ['sometimes', 'nullable', 'string', 'max:120'],
            'duration_ms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sub_category_id' => ['sometimes', 'uuid', Rule::exists('sub_categories', 'id')],
            'primary_source_id' => ['sometimes', 'uuid', Rule::exists('music_sources', 'id')],
        ];
    }
}
