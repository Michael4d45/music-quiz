<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SubCategoryData extends Data
{
    public function __construct(
        public string $id,
        public string $category_id,
        public string $name,
        public string|null $description,
        public int $sort_order,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var CategoryData $category */
        #[AutoWhenLoadedLazy]
        public CategoryData|Lazy $category,
        /** @var Collection<array-key,MusicTrackData> $music_tracks */
        #[AutoWhenLoadedLazy("musicTracks")]
        public Collection|Lazy $music_tracks,
    ) {}
}
