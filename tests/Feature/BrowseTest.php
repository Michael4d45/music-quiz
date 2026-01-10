<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\MusicTrack;
use App\Models\Playlist;
use App\Models\SubCategory;
use App\Models\User;

it('returns browse index data', function () {
    $response = $this->getJson('/api/browse');

    $response->assertSuccessful()->assertJsonStructure([
        'categories',
        'featured_playlists',
        'recent_tracks'
    ]);
});

it('returns categories list', function () {
    $response = $this->getJson('/api/browse/categories');

    $response->assertSuccessful()->assertJsonStructure([
        'categories' => [
            '*' => [
                'id',
                'name',
                'description'
            ]
        ]
    ]);
});

it('returns category details', function () {
    $category = Category::factory()->create();
    $subCategory = SubCategory::factory()->create(['category_id' => $category->id]);

    $response = $this->getJson("/api/browse/categories/{$category->id}");

    $response->assertSuccessful()->assertJsonStructure([
        'category' => [
            'id',
            'name',
            'sub_categories' => [
                '*' => [
                    'id',
                    'name'
                ]
            ]
        ]
    ]);
});

it('returns tracks list', function () {
    $response = $this->getJson('/api/browse/tracks');

    $response->assertSuccessful()->assertJsonStructure([
        'tracks' => [
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'artist_name'
                ]
            ]
        ]
    ]);
});

it('returns track details', function () {
    $track = MusicTrack::factory()->create();

    $response = $this->getJson("/api/browse/tracks/{$track->id}");

    $response->assertSuccessful()->assertJsonStructure([
        'track' => [
            'id',
            'title',
            'artist_name'
        ]
    ]);
});

it('returns public playlists', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create([
        'user_id' => $user->id,
        'is_public' => true
    ]);

    $response = $this->getJson('/api/browse/playlists');

    $response->assertSuccessful()->assertJsonStructure([
        'playlists' => [
            '*' => [
                'id',
                'name',
                'user' => [
                    'id',
                    'name'
                ]
            ]
        ]
    ]);
});

it('returns 404 for non-existent category', function () {
    $response = $this->getJson('/api/browse/categories/99999');

    $response->assertNotFound();
});

it('returns 404 for non-existent track', function () {
    $response = $this->getJson('/api/browse/tracks/99999');

    $response->assertNotFound();
});