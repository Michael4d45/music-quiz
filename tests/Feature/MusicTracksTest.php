<?php

declare(strict_types=1);

use App\Models\MusicSource;
use App\Models\MusicTrack;
use App\Models\SubCategory;
use App\Models\User;

it('returns user music tracks', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $track = MusicTrack::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson('/api/music-tracks');

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'music_tracks' => [
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'artist_name',
                        'user_id',
                    ],
                ],
            ],
        ]);
});

it('creates a new music track', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $subCategory = SubCategory::factory()->create();
    $musicSource = MusicSource::factory()->create();

    $trackData = [
        'title' => 'Test Song',
        'artist_name' => 'Test Artist',
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
        'album_name' => 'Test Album',
        'release_year' => 2023,
        'genre' => 'Pop',
        'duration_ms' => 180000,
    ];

    $response = $this->withToken($token)->postJson(
        '/api/music-tracks',
        $trackData,
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'music_track' => [
                'id',
                'title',
                'artist_name',
                'album_name',
                'user_id',
            ],
        ]);

    $this->assertDatabaseHas('music_tracks', [
        'title' => 'Test Song',
        'artist_name' => 'Test Artist',
        'user_id' => $user->id,
    ]);
});

it('validates required fields when creating track', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/music-tracks', []);

    $response
        ->assertUnprocessable()
        ->assertJsonStructure([
            'message',
            'errors' => [
                'title',
                'artist_name',
                'sub_category_id',
                'primary_source_id',
            ],
        ]);
});

it('returns unauthorized when not authenticated', function () {
    $response = $this->getJson('/api/music-tracks');

    $response->assertUnauthorized();
});

it('returns user music tracks for quiz questions', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $track = MusicTrack::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson('/api/music-tracks/user');

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'tracks' => [
                '*' => [
                    'id',
                    'title',
                    'artist_name',
                    'user_id',
                ],
            ],
        ]);
});
