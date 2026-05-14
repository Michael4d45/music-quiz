<?php

declare(strict_types=1);

namespace App\Features\Reference\Actions;

use App\Data\Models\IdLabelOptionData;
use App\Data\Responses\SubCategoriesListResponseData;
use App\Models\SubCategory;
use Symfony\Component\HttpFoundation\Response;

class ListSubCategories
{
    public function __invoke(): Response
    {
        $rows = SubCategory::query()
            ->with('category')
            ->join(
                'categories',
                'categories.id',
                '=',
                'sub_categories.category_id',
            )
            ->orderBy('categories.name')
            ->orderBy('sub_categories.name')
            ->select('sub_categories.*')
            ->get();

        return response()->json(SubCategoriesListResponseData::from([
            'sub_categories' => IdLabelOptionData::collect(
                $rows->map(static function (SubCategory $sub): array {
                    $categoryName = $sub->category->name ?? 'Category';

                    return [
                        'id' => $sub->id,
                        'label' => "{$categoryName} › {$sub->name}",
                    ];
                })->all(),
                'array',
            ),
        ]));
    }
}
