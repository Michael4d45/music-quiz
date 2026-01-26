<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class PlaylistItemData extends Data
{
    public function __construct(
        public string $id,
        public string $playlist_id,
        public string $question_id,
        public int $sort_order,
        public Carbon $added_at,
        /** @var PlaylistData|Optional $playlist */
        public Optional|PlaylistData $playlist,
        /** @var QuizQuestionData|Optional $question */
        public Optional|QuizQuestionData $question,
    ) {}
}
