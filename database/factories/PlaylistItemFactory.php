<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaylistItem>
 */
class PlaylistItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'playlist_id' => \App\Models\Playlist::factory(),
            'question_id' => \App\Models\QuizQuestion::factory(),
            'sort_order' => fake()->numberBetween(1, 50) * 100,
            'added_at' => now(),
        ];
    }
}
