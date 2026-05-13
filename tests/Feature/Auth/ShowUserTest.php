<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;

test('it can fetch the authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertSuccessful();
    $response->assertJson([
        'id' => $user->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('it creates and returns a guest user when no session user exists', function () {
    $response = $this->getJson('/api/user');

    $response->assertSuccessful();
    $response->assertJsonPath('is_guest', true);
});

test('it returns guest for first-party api request with origin header', function () {
    $response = $this->withHeader('Origin', rtrim(
        (string) config('app.url'),
        '/',
    ))->getJson('/api/user');

    $response->assertSuccessful();
    $response->assertJsonPath('is_guest', true);
});
