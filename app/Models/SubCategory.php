<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * SubCategory model for music sub-categories within categories.
 *
 * @property string $id
 * @property string $category_id
 * @property string $name
 * @property string|null $description
 * @property int $sort_order
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Category $category
 * @property-read Collection<array-key, MusicTrack> $musicTracks
 */
class SubCategory extends Model
{
    /** @use HasFactory<\Database\Factories\SubCategoryFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
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
     * Get the category that owns this sub-category.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all music tracks for this sub-category.
     *
     * @return HasMany<MusicTrack, $this>
     */
    public function musicTracks(): HasMany
    {
        return $this->hasMany(MusicTrack::class);
    }
}
