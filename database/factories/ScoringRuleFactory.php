<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScoringRule>
 */
class ScoringRuleFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->optional()->words(2, true),
            'base_points' => fake()->numberBetween(100, 1000),
            'decay_factor' => fake()->optional()->randomFloat(2, 0.5, 1.0),
            'max_time_ms' => fake()->optional()->numberBetween(10000, 60000),
            'streak_bonus_enabled' => fake()->boolean(),
            'streak_multiplier' => fake()->randomFloat(1, 1.0, 2.0),
        ];
    }
}
