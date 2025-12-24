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
 * QuizMode model for defining how questions are answered.
 *
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property bool $allows_host_override
 * @property bool $requires_manual_scoring
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 * @property-read Collection<array-key, GameSession> $gameSessions
 */
class QuizMode extends Model
{
    /** @use HasFactory<\Database\Factories\QuizModeFactory> */
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
        'allows_host_override',
        'requires_manual_scoring',
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
            'allows_host_override' => 'boolean',
            'requires_manual_scoring' => 'boolean',
        ];
    }

    /**
     * Get all game sessions that use this quiz mode.
     *
     * @return HasMany<GameSession, $this>
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'quiz_mode_id');
    }
}
