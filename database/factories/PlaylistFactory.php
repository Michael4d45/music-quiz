<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Playlist>
 */
class PlaylistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'visibility' => fake()->randomElement(Visibility::cases()),
            'tags' => fake()->words(5),
            'estimated_duration_minutes' => fake()->numberBetween(10, 120),
            'target_audience' => fake()->randomElement([
                'Beginners',
                'Experts',
                'Casual Players',
            ]),
            'question_order' => fake()->randomElement(['fixed', 'random']),
            'default_time_limit_seconds' => fake()->numberBetween(10, 60),
            'play_count' => fake()->numberBetween(0, 100),
        ];
    }
}
