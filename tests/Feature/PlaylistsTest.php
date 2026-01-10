<?php

declare(strict_types=1);

use App\Models\Playlist;
use App\Models\User;

it('returns user playlists', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson('/api/playlists');

    $response->assertSuccessful()->assertJsonStructure([
        'playlists' => [
            '*' => [
                'id',
                'name',
                'user_id'
            ]
        ]
    ]);
});

it('creates a new playlist', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $playlistData = [
        'name' => 'My Awesome Playlist',
        'description' => 'A test playlist',
        'is_public' => true
    ];

    $response = $this->withToken($token)->postJson('/api/playlists', $playlistData);

    $response->assertSuccessful()->assertJsonStructure([
        'playlist' => [
            'id',
            'name',
            'description',
            'is_public',
            'user_id'
        ]
    ]);

    $this->assertDatabaseHas('playlists', [
        'name' => 'My Awesome Playlist',
        'user_id' => $user->id
    ]);
});

it('returns playlist details', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson("/api/playlists/{$playlist->id}");

    $response->assertSuccessful()->assertJsonStructure([
        'playlist' => [
            'id',
            'name',
            'user_id'
        ]
    ]);
});

it('updates a playlist', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $playlist = Playlist::factory()->create([
        'user_id' => $user->id,
        'name' => 'Original Name'
    ]);

    $updateData = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'is_public' => false
    ];

    $response = $this->withToken($token)->putJson("/api/playlists/{$playlist->id}", $updateData);

    $response->assertSuccessful()->assertJsonStructure([
        'playlist' => [
            'id',
            'name',
            'description'
        ]
    ]);

    $this->assertDatabaseHas('playlists', [
        'id' => $playlist->id,
        'name' => 'Updated Name'
    ]);
});

it('returns 404 for non-existent playlist', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/playlists/99999');

    $response->assertNotFound();
});

it('returns 403 when updating another users playlist', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $token = $user2->createToken('test-token')->plainTextToken;

    $playlist = Playlist::factory()->create(['user_id' => $user1->id]);

    $response = $this->withToken($token)->putJson("/api/playlists/{$playlist->id}", [
        'name' => 'Hacked Name'
    ]);

    $response->assertForbidden();
});

it('returns unauthorized when not authenticated', function () {
    $response = $this->getJson('/api/playlists');

    $response->assertUnauthorized();
});

it('returns user playlists for session creation', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson('/api/playlists/user/list');

    $response->assertSuccessful()->assertJsonStructure([
        'playlists' => [
            '*' => [
                'id',
                'name',
                'user_id'
            ]
        ]
    ]);
});