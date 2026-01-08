<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Enums\Visibility;
use App\Models\MusicTrack;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access quiz questions page', function (): void {
    $user = User::factory()->create();
    QuizQuestion::factory(5)->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->get(route('quiz-questions.index'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('quiz-questions/index'));
});

it('can create a quiz question', function (): void {
    $user = User::factory()->create();
    $track = MusicTrack::factory()->create();

    $response = $this->actingAs($user)->post(route('quiz-questions.store'), [
        'track_id' => $track->id,
        'question_type' => QuestionType::Artist->value,
        'prompt_text' => 'Who is the artist?',
        'correct_answer' => 'Test Artist',
        'base_points' => 1500,
        'media_start_seconds' => 30,
        'media_end_seconds' => 60,
        'difficulty_level' => 3,
        'visibility' => Visibility::Public->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('quiz_questions', [
        'user_id' => $user->id,
        'track_id' => $track->id,
        'question_type' => QuestionType::Artist->value,
        'prompt_text' => 'Who is the artist?',
        'correct_answer' => 'Test Artist',
        'base_points' => 1500,
        'media_start_seconds' => 30,
        'media_end_seconds' => 60,
        'difficulty_level' => 3,
        'visibility' => Visibility::Public->value,
    ]);
});

it('can create a quiz question without track', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('quiz-questions.store'), [
        'question_type' => QuestionType::Title->value,
        'correct_answer' => 'Test Title',
        'visibility' => Visibility::Private->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('quiz_questions', [
        'user_id' => $user->id,
        'track_id' => null,
        'question_type' => QuestionType::Title->value,
        'correct_answer' => 'Test Title',
        'visibility' => Visibility::Private->value,
    ]);
});

it('validates required fields when creating quiz question', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('quiz-questions.store'), []);

    $response->assertSessionHasErrors([
        'question_type',
        'correct_answer',
    ]);
});

it('validates foreign key constraints when creating quiz question', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('quiz-questions.store'), [
        'track_id' => 999,
        'question_type' => QuestionType::Artist->value,
        'correct_answer' => 'Test Answer',
    ]);

    $response->assertSessionHasErrors(['track_id']);
});

it('validates points range when creating quiz question', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('quiz-questions.store'), [
        'question_type' => QuestionType::Artist->value,
        'correct_answer' => 'Test Answer',
        'base_points' => 0,
    ]);

    $response->assertSessionHasErrors(['base_points']);
});

it('validates difficulty level range when creating quiz question', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('quiz-questions.store'), [
        'question_type' => QuestionType::Artist->value,
        'correct_answer' => 'Test Answer',
        'difficulty_level' => 6,
    ]);

    $response->assertSessionHasErrors(['difficulty_level']);
});

it('can access create quiz question page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('quiz-questions.create'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('quiz-questions/create'));
});

it('requires authentication for quiz questions', function (): void {
    $response = $this->get(route('quiz-questions.index'));
    $response->assertRedirect(route('login'));
});
