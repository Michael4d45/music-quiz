<?php

declare(strict_types=1);

namespace App\Features\Playlists\Requests;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;

class StorePlaylistItemRequest extends Data
{
    public function __construct(
        #[Required, Uuid, Exists('quiz_questions', 'id')]
        public string $question_id,
    ) {}
}
