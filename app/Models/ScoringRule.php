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
 * ScoringRule model for defining scoring mechanics.
 *
 * @property string $id
 * @property string|null $name
 * @property int $base_points
 * @property float|null $decay_factor
 * @property int|null $max_time_ms
 * @property bool $streak_bonus_enabled
 * @property float $streak_multiplier
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Collection<array-key, GameSession> $gameSessions
 */
class ScoringRule extends Model
{
    /** @use HasFactory<\Database\Factories\ScoringRuleFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<model-property<self>>
     */
    protected $fillable = [
        'name',
        'base_points',
        'decay_factor',
        'max_time_ms',
        'streak_bonus_enabled',
        'streak_multiplier',
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
            'base_points' => 'integer',
            'decay_factor' => 'float',
            'max_time_ms' => 'integer',
            'streak_bonus_enabled' => 'boolean',
            'streak_multiplier' => 'float',
        ];
    }

    /**
     * Get all game sessions that use this scoring rule.
     *
     * @return HasMany<GameSession, $this>
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'scoring_rule_id');
    }
}
