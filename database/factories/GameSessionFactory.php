<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameSession>
 */
class GameSessionFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'room_code' => fake()->unique()->bothify('???###'),
            'status' => \App\Enums\SessionStatus::Lobby,
            'max_players' => fake()->numberBetween(2, 20),
        ];
    }
}
