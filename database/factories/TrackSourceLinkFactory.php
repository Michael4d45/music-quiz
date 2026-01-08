<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackSourceLink>
 */
class TrackSourceLinkFactory extends Factory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->uuid(),
            'preview_url' => fake()->optional()->url(),
            'full_url' => fake()->optional()->url(),
            'embed_url' => fake()->optional()->url(),
            'album_art_url' => fake()->optional()->url(),
            'is_verified' => fake()->boolean(),
            'is_available' => fake()->boolean(),
            'last_checked_at' => fake()->optional()->dateTime(),
        ];
    }
}
