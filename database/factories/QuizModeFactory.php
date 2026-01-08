<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizMode>
 */
class QuizModeFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'allows_host_override' => fake()->boolean(),
            'requires_manual_scoring' => fake()->boolean(),
        ];
    }
}
