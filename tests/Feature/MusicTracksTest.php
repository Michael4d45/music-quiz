<?php

declare(strict_types=1);

use App\Models\MusicSource;
use App\Models\MusicTrack;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access music tracks page', function (): void {
    $user = User::factory()->create();
    MusicTrack::factory(5)->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->get(route('music-tracks.index'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('music-tracks/index'));
});

it('can create a music track', function (): void {
    $user = User::factory()->create();
    $subCategory = SubCategory::factory()->create();
    $musicSource = MusicSource::factory()->create();

    $response = $this->actingAs($user)->post(route('music-tracks.store'), [
        'title' => 'Test Track',
        'artist_name' => 'Test Artist',
        'album_name' => 'Test Album',
        'release_year' => 2023,
        'genre' => 'Rock',
        'duration_ms' => 180000,
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('music_tracks', [
        'user_id' => $user->id,
        'title' => 'Test Track',
        'artist_name' => 'Test Artist',
        'album_name' => 'Test Album',
        'release_year' => 2023,
        'genre' => 'Rock',
        'duration_ms' => 180000,
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);
});

it('validates required fields when creating music track', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('music-tracks.store'), []);

    $response->assertSessionHasErrors([
        'title',
        'artist_name',
        'sub_category_id',
        'primary_source_id',
    ]);
});

it('validates foreign key constraints when creating music track', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('music-tracks.store'), [
        'title' => 'Test Track',
        'artist_name' => 'Test Artist',
        'sub_category_id' => 999,
        'primary_source_id' => 999,
    ]);

    $response->assertSessionHasErrors([
        'sub_category_id',
        'primary_source_id',
    ]);
});

it('validates year range when creating music track', function (): void {
    $user = User::factory()->create();
    $subCategory = SubCategory::factory()->create();
    $musicSource = MusicSource::factory()->create();

    $response = $this->actingAs($user)->post(route('music-tracks.store'), [
        'title' => 'Test Track',
        'artist_name' => 'Test Artist',
        'release_year' => 1899,
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);

    $response->assertSessionHasErrors(['release_year']);
});

it('can access create music track page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('music-tracks.create'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('music-tracks/create'));
});

it('requires authentication for music tracks', function (): void {
    $response = $this->get(route('music-tracks.index'));
    $response->assertRedirect(route('login'));
});
