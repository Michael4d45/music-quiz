<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Models\MusicSource;
use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays track information for music track', function (): void {
    $user = User::factory()->create(['is_admin' => true]);
    $category = \App\Models\Category::factory()->create();
    $subCategory = \App\Models\SubCategory::factory()->create([
        'category_id' => $category->id,
    ]);
    $source = MusicSource::factory()->create([
        'display_name' => 'Spotify',
        'icon_url' => 'https://spotify.com/icon.png',
    ]);

    $track = MusicTrack::factory()->create([
        'title' => 'Test Song',
        'artist_name' => 'Test Artist',
        'album_name' => 'Test Album',
        'release_year' => 2023,
        'genre' => 'Pop',
        'sub_category_id' => $subCategory->id,
        'primary_source_id' => $source->id,
    ]);

    $response = $this->actingAs($user)->get(route(
        'filament.admin.resources.music-tracks.edit',
        $track,
    ));

    $response->assertStatus(200);

    // For now, just check that we get a response containing the track title
    expect($response->getContent())->toContain('Test Song');
});

it('displays track information for quiz question', function (): void {
    $user = User::factory()->create(['is_admin' => true]);
    $track = MusicTrack::factory()->create([
        'title' => 'Quiz Song',
        'artist_name' => 'Quiz Artist',
    ]);

    $question = QuizQuestion::factory()->create([
        'track_id' => $track->id,
        'question_type' => QuestionType::Artist,
        'prompt_text' => 'Who sings this song?',
        'correct_answer' => 'Quiz Artist',
        'media_start_seconds' => 30,
        'media_end_seconds' => 45,
    ]);

    $response = $this->actingAs($user)->get(route(
        'filament.admin.resources.quiz-questions.edit',
        $question,
    ));

    $response->assertStatus(200);
    expect($response->getContent())->toContain('Quiz Song');
});
