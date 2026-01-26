<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ScoringRuleData extends Data
{
    public function __construct(
        public string $id,
        public null|string $name,
        public int $base_points,
        public null|float $decay_factor,
        public null|int $max_time_ms,
        public bool $streak_bonus_enabled,
        public float $streak_multiplier,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var Collection<array-key,GameSessionData>|Optional */
        public Collection|Optional $game_sessions,
    ) {}
}
