<?php

declare(strict_types=1);

namespace App\Features\Playlists\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderPlaylistItemsRequest extends FormRequest
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
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['uuid', 'distinct'],
        ];
    }
}
