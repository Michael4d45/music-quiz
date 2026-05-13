<?php

declare(strict_types=1);

namespace App\Data\Models;

use App\Enums\MusicTrackOriginKind;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class MusicTrackData extends Data
{
    public function __construct(
        public string $id,
        public null|string $user_id,
        public string $title,
        public string $artist_name,
        public null|string $album_name,
        public null|int $release_year,
        public null|string $genre,
        public null|int $duration_ms,
        public null|MusicTrackOriginKind $origin_kind,
        public null|string $origin_title,
        public null|string $user_upload_path,
        public null|string $user_upload_original_name,
        public string $sub_category_id,
        public string $primary_source_id,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var UserData|null|Optional $user */
        public Optional|UserData|null $user,
        /** @var SubCategoryData|Optional $sub_category */
        public Optional|SubCategoryData $sub_category,
        /** @var MusicSourceData|Optional $primary_source */
        public Optional|MusicSourceData $primary_source,
        /** @var Collection<array-key,TrackSourceLinkData>|Optional */
        public Collection|Optional $source_links,
        /** @var Collection<array-key,QuizQuestionData>|Optional */
        public Collection|Optional $quiz_questions,
    ) {}
}
