<?php

declare(strict_types=1);

use App\Models\User;

it('returns home data', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/home');

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'statistic',
            'recent_sessions',
            'recent_playlists',
        ]);
});

it('returns guest home data when not authenticated', function () {
    $response = $this->getJson('/api/home');

    $response->assertSuccessful();
});
