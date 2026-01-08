<?php

declare(strict_types=1);

namespace App\Actions\Browse;

use App\Data\Models\CategoryData;
use App\Data\Response\CategoriesResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class ShowCategories
{
    /**
     * Display the categories list.
     */
    public function __invoke(): JsonResponse
    {
        $categories = Category::with('subCategories')->orderBy(
            'sort_order',
        )->get();

        return response()->json(CategoriesResponse::from([
            'categories' => CategoryData::collect($categories),
        ]));
    }
}
