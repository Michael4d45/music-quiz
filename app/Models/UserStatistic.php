<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * UserStatistic model for aggregated user performance statistics.
 *
 * @property string $id
 * @property string $user_id
 * @property int $total_games_played
 * @property int $total_wins
 * @property int $total_points
 * @property float|null $average_score
 * @property int $best_streak
 * @property string|null $favorite_category_id
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read User $user
 * @property-read Category|null $favoriteCategory
 */
class UserStatistic extends Model
{
    /** @use HasFactory<\Database\Factories\UserStatisticFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'user_id',
        'total_games_played',
        'total_wins',
        'total_points',
        'average_score',
        'best_streak',
        'favorite_category_id',
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
            'total_games_played' => 'integer',
            'total_wins' => 'integer',
            'total_points' => 'integer',
            'average_score' => 'float',
            'best_streak' => 'integer',
        ];
    }

    /**
     * Get the user that owns these statistics.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the favorite category for this user.
     *
     * @return BelongsTo<Category, $this>
     */
    public function favoriteCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'favorite_category_id');
    }
}
