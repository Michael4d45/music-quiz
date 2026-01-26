<?php

declare(strict_types=1);

namespace App\Data\Models;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserStatisticData extends Data
{
    public function __construct(
        public string $id,
        public string $user_id,
        public int $total_games_played,
        public int $total_wins,
        public int $total_points,
        public null|float $average_score,
        public int $best_streak,
        public null|string $favorite_category_id,
        public null|Carbon $created_at,
        public null|Carbon $updated_at,
        /** @var UserData|Optional $user */
        public Optional|UserData $user,
        /** @var CategoryData|null|Optional $favorite_category */
        public CategoryData|Optional|null $favorite_category,
    ) {}
}
