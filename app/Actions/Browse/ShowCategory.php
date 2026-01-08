<?php

declare(strict_types=1);

namespace App\Actions\Browse;

use App\Data\Models\CategoryData;
use App\Data\Models\MusicTrackData;
use App\Data\Response\CategoryResponse;
use App\Models\Category;
use App\Models\MusicTrack;
use Illuminate\Http\JsonResponse;

class ShowCategory
{
    /**
     * Display the category data.
     */
    public function __invoke(Category $category): JsonResponse
    {
        $category->load('subCategories');

        $tracks = MusicTrack::whereHas('subCategory', function ($query) use (
            $category,
        ) {
            $query->where('sub_categories.category_id', $category->id);
        })
            ->with(['subCategory', 'primarySource'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $questionsCount = MusicTrack::whereHas('subCategory', function ($query) use (
            $category,
        ) {
            $query->where('sub_categories.category_id', $category->id);
        })
            ->withCount('quizQuestions')
            ->get()
            ->sum('quiz_questions_count');

        return response()->json(CategoryResponse::from([
            'category' => CategoryData::from($category),
            'tracks' => MusicTrackData::collect($tracks),
            'questions_count' => $questionsCount,
        ]));
    }
}
