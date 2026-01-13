<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionRound>
 */
class SessionRoundFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'session_id' => GameSession::factory(),
            'question_id' => QuizQuestion::factory(),
            'round_number' => 1,
            'started_at' => null,
            'ended_at' => null,
        ];
    }
}
