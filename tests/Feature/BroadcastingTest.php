<?php

declare(strict_types=1);

use App\Models\User;

it('authenticates broadcasting channel', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $broadcastData = [
        'socket_id' => 'socket123',
        'channel_name' => 'presence-session.ABCD1234'
    ];

    $response = $this->withToken($token)->postJson('/api/broadcasting/auth', $broadcastData);

    $response->assertSuccessful()->assertJsonStructure([
        'auth'
    ]);
});

it('returns unauthorized when not authenticated', function () {
    $broadcastData = [
        'socket_id' => 'socket123',
        'channel_name' => 'presence-session.ABCD1234'
    ];

    $response = $this->postJson('/api/broadcasting/auth', $broadcastData);

    $response->assertUnauthorized();
});