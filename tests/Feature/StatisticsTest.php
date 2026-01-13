<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserStatistic;

it('returns user statistics', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $stats = UserStatistic::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson('/api/statistics');

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'statistic' => [
                'user_id',
                'total_games_played',
                'total_wins',
                'total_points',
                'average_score',
            ],
            'recent_sessions' => [
                '*' => [
                    'id',
                    'room_code',
                    'status',
                ],
            ],
        ]);
});

it('returns empty stats when user has no statistics', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/statistics');

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'statistic',
            'recent_sessions',
        ]);
});

it('returns unauthorized when not authenticated', function () {
    $response = $this->getJson('/api/statistics');

    $response->assertUnauthorized();
});
