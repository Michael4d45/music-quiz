<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class MusicTrackData extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public string $artist_name,
        public string|null $album_name,
        public int|null $release_year,
        public string|null $genre,
        public int|null $duration_ms,
        public string $sub_category_id,
        public string $primary_source_id,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var SubCategoryData $sub_category */
        #[AutoWhenLoadedLazy("subCategory")]
        public SubCategoryData|Lazy $sub_category,
        /** @var MusicSourceData $primary_source */
        #[AutoWhenLoadedLazy("primarySource")]
        public MusicSourceData|Lazy $primary_source,
        /** @var Collection<array-key,TrackSourceLinkData> $source_links */
        #[AutoWhenLoadedLazy("sourceLinks")]
        public Collection|Lazy $source_links,
        /** @var Collection<array-key,QuizQuestionData> $quiz_questions */
        #[AutoWhenLoadedLazy("quizQuestions")]
        public Collection|Lazy $quiz_questions,
    ) {}
}
