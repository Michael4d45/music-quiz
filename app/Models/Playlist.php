<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionOrder;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Playlist model for user-created question collections.
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string|null $description
 * @property Visibility $visibility
 * @property list<string>|null $tags
 * @property int|null $estimated_duration_minutes
 * @property string|null $target_audience
 * @property QuestionOrder $question_order
 * @property int|null $default_time_limit_seconds
 * @property string|null $scoring_rule_id
 * @property int $play_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<array-key,PlaylistItem> $items
 * @property-read Collection<array-key,GameSession> $gameSessions
 */
class Playlist extends Model
{
    /** @use HasFactory<\Database\Factories\PlaylistFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'visibility',
        'tags',
        'estimated_duration_minutes',
        'target_audience',
        'question_order',
        'default_time_limit_seconds',
        'scoring_rule_id',
        'play_count',
    ];

    /**
     * Get all game sessions that used this playlist.
     *
     * @return HasMany<GameSession, $this>
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'playlist_id');
    }

    /**
     * Get all items in this playlist.
     *
     * @return HasMany<PlaylistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PlaylistItem::class)->orderBy('sort_order');
    }

    /**
     * Get the user that owns this playlist.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<model-property<self>, mixed>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
            'question_order' => QuestionOrder::class,
            'tags' => 'array',
            'estimated_duration_minutes' => 'integer',
            'default_time_limit_seconds' => 'integer',
            'play_count' => 'integer',
        ];
    }
}
