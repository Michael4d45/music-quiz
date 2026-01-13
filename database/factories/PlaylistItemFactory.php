<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Playlist;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaylistItem>
 */
class PlaylistItemFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'playlist_id' => Playlist::factory(),
            'question_id' => QuizQuestion::factory(),
            'sort_order' => fake()->numberBetween(1, 100),
            'added_at' => now(),
        ];
    }
}
