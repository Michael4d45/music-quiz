<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionParticipant>
 */
class SessionParticipantFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'role' => Role::Player,
            'current_total_score' => 0,
            'is_connected' => true,
            'joined_at' => now(),
        ];
    }
}
