<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserStatistic;

it('returns leaderboard data', function () {
    $user = User::factory()->create();
    UserStatistic::factory()->create(['user_id' => $user->id]);

    $response = $this->getJson('/api/leaderboard');

    $response->assertSuccessful()->assertJsonStructure([
        'players' => [
            '*' => [
                'user' => [
                    'id',
                    'name'
                ],
                'total_points',
                'total_games_played'
            ]
        ]
    ]);
});

it('returns empty leaderboard when no stats exist', function () {
    $response = $this->getJson('/api/leaderboard');

    $response->assertSuccessful()->assertJsonStructure([
        'players' => []
    ]);
});