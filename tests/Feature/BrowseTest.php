<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access browse page', function (): void {
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = \App\Models\MusicSource::factory()->create();
    \App\Models\MusicTrack::factory(3)->create([
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);
    $user = \App\Models\User::factory()->create();
    \App\Models\Playlist::factory(3)->create([
        'user_id' => $user->id,
        'is_public' => true,
    ]);

    $response = $this->get(route('browse'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/index'));
});

it('can access categories page', function (): void {
    \App\Models\Category::factory(5)->create();

    $response = $this->get(route('browse.categories'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/categories'));
});

it('can access category page', function (): void {
    $category = \App\Models\Category::factory()->create();

    $response = $this->get(route('browse.category', $category));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/category'));
});

it('can access tracks page', function (): void {
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = \App\Models\MusicSource::factory()->create();
    \App\Models\MusicTrack::factory(5)->create([
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);

    $response = $this->get(route('browse.tracks'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/tracks'));
});

it('can search tracks by title', function (): void {
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = \App\Models\MusicSource::factory()->create();
    $track1 = \App\Models\MusicTrack::factory()->create([
        'title' => 'Test Song',
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);
    $track2 = \App\Models\MusicTrack::factory()->create([
        'title' => 'Other Song',
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);

    $response = $this->get(route('browse.tracks', ['search' => 'Test']));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page->component('browse/tracks')->has(
                'tracks.data',
                1,
            )->where('tracks.data.0.title', 'Test Song'),
        );
});

it('can paginate tracks', function (): void {
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = \App\Models\MusicSource::factory()->create();
    \App\Models\MusicTrack::factory(30)->create([
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);

    $response = $this->get(route('browse.tracks', ['page' => 2]));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page
                ->component('browse/tracks')
                ->has('tracks')
                ->has('tracks.meta')
                ->where('tracks.meta.current_page', 2)
                ->has('tracks.data'),
        );
});

it('can access track page', function (): void {
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = \App\Models\MusicSource::factory()->create();
    $track = \App\Models\MusicTrack::factory()->create([
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);
    $response = $this->get(route('browse.track', $track));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/track'));
});

it('displays track questions on track detail page', function (): void {
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = \App\Models\MusicSource::factory()->create();
    $track = \App\Models\MusicTrack::factory()->create([
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);
    $question1 = \App\Models\QuizQuestion::factory()->create([
        'track_id' => $track->id,
    ]);
    $question2 = \App\Models\QuizQuestion::factory()->create([
        'track_id' => $track->id,
    ]);

    $response = $this->get(route('browse.track', $track));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page->component('browse/track')->has(
                'track.quiz_questions',
                2,
            ),
        );
});

it('can access public playlists page', function (): void {
    $user = \App\Models\User::factory()->create();
    \App\Models\Playlist::factory(5)->create([
        'user_id' => $user->id,
        'is_public' => true,
    ]);

    $response = $this->get(route('browse.playlists'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/playlists'));
});

it('only shows public playlists on browse page', function (): void {
    $user = \App\Models\User::factory()->create();
    $publicPlaylist = \App\Models\Playlist::factory()->create([
        'user_id' => $user->id,
        'is_public' => true,
    ]);
    $privatePlaylist = \App\Models\Playlist::factory()->create([
        'user_id' => $user->id,
        'is_public' => false,
    ]);

    $response = $this->get(route('browse.playlists'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('browse/playlists')->has(
            'playlists.data',
            1,
        )->where('playlists.data.0.id', $publicPlaylist->id));
});

it('can paginate public playlists', function (): void {
    $user = \App\Models\User::factory()->create();
    \App\Models\Playlist::factory(30)->create([
        'user_id' => $user->id,
        'is_public' => true,
    ]);

    $response = $this->get(route('browse.playlists', ['page' => 2]));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page
                ->component('browse/playlists')
                ->has('playlists')
                ->has('playlists.meta')
                ->where('playlists.meta.current_page', 2)
                ->has('playlists.data'),
        );
});
