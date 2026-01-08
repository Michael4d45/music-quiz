<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
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
        /** @var PlaylistData $playlist */
        #[AutoWhenLoadedLazy]
        public Optional|PlaylistData $playlist,
        /** @var QuizQuestionData $question */
        #[AutoWhenLoadedLazy]
        public Optional|QuizQuestionData $question,
    ) {}
}
