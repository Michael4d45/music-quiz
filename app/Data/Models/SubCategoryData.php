<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SubCategoryData extends Data
{
    public function __construct(
        public string $id,
        public string $category_id,
        public string $name,
        public null|string $description,
        public int $sort_order,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var CategoryData|Optional $category */
        public CategoryData|Optional $category,
        /** @var Collection<array-key,MusicTrackData>|Optional */
        public Collection|Optional $music_tracks,
    ) {}
}
