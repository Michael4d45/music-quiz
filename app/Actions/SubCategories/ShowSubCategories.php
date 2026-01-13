<?php

declare(strict_types=1);

namespace App\Actions\SubCategories;

use App\Data\Models\SubCategoryData;
use App\Data\Response\SubCategoriesResponse;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;

class ShowSubCategories
{
    /**
     * Display all subcategories.
     */
    public function __invoke(): JsonResponse
    {
        $subCategories = SubCategory::with('category')->orderBy('name')->get();

        return response()->json(SubCategoriesResponse::from([
            'sub_categories' => SubCategoryData::collect($subCategories),
        ]));
    }
}
