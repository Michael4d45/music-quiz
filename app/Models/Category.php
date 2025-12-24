<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Category model for top-level music categories.
 *
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon_url
 * @property int $sort_order
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Collection<array-key, SubCategory> $subCategories
 * @property-read Collection<array-key, UserStatistic> $userStatistics
 */
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'name',
        'description',
        'icon_url',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<model-property<self>, mixed>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get all sub-categories for this category.
     *
     * @return HasMany<SubCategory, $this>
     */
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class)->orderBy('sort_order');
    }

    /**
     * Get all user statistics for this category.
     *
     * @return HasMany<UserStatistic, $this>
     */
    public function userStatistics(): HasMany
    {
        return $this->hasMany(UserStatistic::class, 'favorite_category_id');
    }
}
