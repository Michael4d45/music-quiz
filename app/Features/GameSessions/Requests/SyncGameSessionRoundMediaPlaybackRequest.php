<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncGameSessionRoundMediaPlaybackRequest extends FormRequest
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
            'playing' => ['required', 'boolean'],
            'current_time_seconds' => ['required', 'numeric', 'min:0'],
        ];
    }
}
