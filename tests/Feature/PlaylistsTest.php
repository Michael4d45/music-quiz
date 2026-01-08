<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Models\Category;
use App\Models\MusicSource;
use App\Models\MusicTrack;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access playlists page', function (): void {
    $user = User::factory()->create();
    Playlist::factory(5)->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->get(route('playlists.index'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('playlists/index'));
});

it('can access create playlist page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('playlists.create'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('playlists/create'));
});

it('can create a playlist with questions', function (): void {
    $user = User::factory()->create();
    $question1 = QuizQuestion::factory()->create();
    $question2 = QuizQuestion::factory()->create();

    $response = $this->actingAs($user)->post(route('playlists.store'), [
        'name' => 'My Test Playlist',
        'description' => 'Test description',
        'is_public' => true,
        'question_ids' => [$question1->id, $question2->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('playlists', [
        'user_id' => $user->id,
        'name' => 'My Test Playlist',
        'description' => 'Test description',
        'is_public' => true,
    ]);

    $playlist = Playlist::where('name', 'My Test Playlist')->first();
    expect($playlist)->not->toBeNull();
    expect($playlist->items)->toHaveCount(2);
    expect($playlist->items->pluck('question_id')->toArray())
        ->toContain($question1->id, $question2->id);
});

it('validates required fields when creating playlist', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('playlists.store'), []);

    $response->assertSessionHasErrors(['name']);
});

it('can access playlist page', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->get(route('playlists.show', $playlist));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('playlists/show'));
});

it('displays playlist items on show page', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);
    $question = QuizQuestion::factory()->create();
    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($user)->get(route('playlists.show', $playlist));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page->component('playlists/show')->has(
                'playlist.items',
                1,
            ),
        );
});

it('can access edit playlist page', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->get(route('playlists.edit', $playlist));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('playlists/edit'));
});

it('can update a playlist', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'is_public' => false,
    ]);
    $question1 = QuizQuestion::factory()->create();
    $question2 = QuizQuestion::factory()->create();

    $response = $this->actingAs($user)->put(
        route('playlists.update', $playlist),
        [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'is_public' => true,
            'question_ids' => [$question1->id, $question2->id],
        ],
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('playlists', [
        'id' => $playlist->id,
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'is_public' => true,
    ]);

    $playlist->refresh();
    expect($playlist->items)->toHaveCount(2);
});

it('prevents users from editing other users playlists', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user1->id]);
    $question1 = QuizQuestion::factory()->create();
    $question2 = QuizQuestion::factory()->create();

    $response = $this->actingAs($user2)->put(
        route('playlists.update', $playlist),
        [
            'name' => 'Hacked Name',
            'description' => null,
            'is_public' => false,
            'question_ids' => [$question1->id, $question2->id],
        ],
    );

    $response->assertForbidden();
});

it('can create a playlist with both existing and new questions', function (): void {
    $user = User::factory()->create();
    $existingQuestion = QuizQuestion::factory()->create();

    $category = Category::factory()->create();
    $subCategory = SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $musicSource = MusicSource::factory()->create();
    $track = MusicTrack::factory()->create([
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $musicSource->id,
    ]);

    $response = $this->actingAs($user)->post(route('playlists.store'), [
        'name' => 'Mixed Playlist',
        'description' => null,
        'is_public' => true,
        'question_ids' => [$existingQuestion->id],
        'new_questions' => [
            [
                'track_id' => $track->id,
                'question_type' => QuestionType::Artist->value,
                'prompt_text' => 'Who is the artist?',
                'correct_answer' => 'Test Artist',
                'base_points' => 15,
                'difficulty_level' => 3,
            ],
        ],
    ]);

    $response->assertRedirect();
    $playlist = Playlist::where('name', 'Mixed Playlist')->first();
    expect($playlist)->not->toBeNull();
    expect($playlist->items)->toHaveCount(2);

    $questionIds = $playlist->items->pluck('question_id')->toArray();
    expect($questionIds)->toContain($existingQuestion->id);

    $newQuestion = QuizQuestion::where(
        'correct_answer',
        'Test Artist',
    )->first();
    expect($newQuestion)->not->toBeNull();
    expect($questionIds)->toContain($newQuestion->id);
});

it('validates new question fields when creating playlist', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('playlists.store'), [
        'name' => 'Invalid Playlist',
        'new_questions' => [
            [
                'track_id' => '',
                'question_type' => QuestionType::Title->value,
                'correct_answer' => '',
            ],
        ],
    ]);

    $response->assertSessionHasErrors([
        'new_questions.0.track_id',
        'new_questions.0.correct_answer',
    ]);
});
