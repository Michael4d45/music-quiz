<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class CategoryData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string|null $description,
        public string|null $icon_url,
        public int $sort_order,
        public Carbon|null $created_at,
        public Carbon|null $updated_at,
        /** @var Collection<array-key,SubCategoryData> $sub_categories */
        #[AutoWhenLoadedLazy('subCategories')]
        public Collection|Lazy $sub_categories,
        /** @var Collection<array-key,UserStatisticData> $user_statistics */
        #[AutoWhenLoadedLazy('userStatistics')]
        public Collection|Lazy $user_statistics,
    ) {}
}
