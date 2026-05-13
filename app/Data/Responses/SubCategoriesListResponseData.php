<?php

declare(strict_types=1);

namespace App\Data\Responses;

use App\Data\Models\IdLabelOptionData;
use Spatie\LaravelData\Data;

class SubCategoriesListResponseData extends Data
{
    /**
     * @param list<IdLabelOptionData> $sub_categories
     */
    public function __construct(
        public array $sub_categories,
    ) {}
}
