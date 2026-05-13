<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMusicTrackRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'artist_name' => ['required', 'string', 'max:255'],
            'album_name' => ['nullable', 'string', 'max:255'],
            'release_year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'genre' => ['nullable', 'string', 'max:120'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'sub_category_id' => ['required', 'uuid', Rule::exists('sub_categories', 'id')],
            'primary_source_id' => ['required', 'uuid', Rule::exists('music_sources', 'id')],
        ];
    }
}
