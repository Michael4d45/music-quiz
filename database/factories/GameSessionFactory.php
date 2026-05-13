<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameSession>
 */
class GameSessionFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'host_id' => User::factory(),
            'room_code' => strtoupper(fake()->unique()->bothify('???###')),
            'status' => SessionStatus::Lobby,
            'quiz_mode_id' => QuizMode::factory(),
            'scoring_rule_id' => ScoringRule::factory(),
            'playlist_id' => null,
            'max_players' => fake()->numberBetween(2, 20),
            'is_public' => false,
        ];
    }

    public function publicLobby(): static
    {
        return $this->state(fn(array $attributes): array => [
            'is_public' => true,
            'status' => SessionStatus::Lobby,
            'started_at' => null,
            'ended_at' => null,
        ]);
    }
}
