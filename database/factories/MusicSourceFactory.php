<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MusicSource>
 */
class MusicSourceFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'display_name' => fake()->words(2, true),
            'requires_authentication' => fake()->boolean(),
            'is_active' => true,
            'priority' => fake()->numberBetween(0, 10),
        ];
    }
}
