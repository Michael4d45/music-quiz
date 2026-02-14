<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MusicTrack>
 */
class MusicTrackFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'artist_name' => fake()->name(),
            'album_name' => fake()->optional()->words(2, true),
            'release_year' => fake()
                ->optional()
                ->numberBetween(1950, now()->year),
            'genre' => fake()->optional()->word(),
            'duration_ms' => fake()->optional()->numberBetween(60_000, 300_000),
            'sub_category_id' => \App\Models\SubCategory::factory(),
            'primary_source_id' => \App\Models\MusicSource::factory(),
        ];
    }
}
