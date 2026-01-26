<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CategoryData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public null|string $description,
        public null|string $icon_url,
        public int $sort_order,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var Collection<array-key,SubCategoryData>|Optional */
        public Collection|Optional $sub_categories,
        /** @var Collection<array-key,UserStatisticData>|Optional */
        public Collection|Optional $user_statistics,
    ) {}
}
