<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\Playlist;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access create session page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('sessions.create'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('sessions/create'));
});

it('can create a game session', function (): void {
    $user = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('sessions.store'), [
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => $playlist->id,
        'max_players' => 10,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('game_sessions', [
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => $playlist->id,
        'max_players' => 10,
        'status' => SessionStatus::Lobby->value,
    ]);

    $session = GameSession::where('host_id', $user->id)->first();
    expect($session)->not->toBeNull();
    expect($session->room_code)->toHaveLength(6);
});

it('validates required fields when creating session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('sessions.store'), []);

    $response->assertSessionHasErrors(['quiz_mode_id', 'scoring_rule_id']);
});

it('can access join session page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('sessions.join'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('sessions/join'));
});

it('prevents joining non-existent session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('sessions.join.store'), [
        'room_code' => 'INVALID',
    ]);

    $response->assertSessionHasErrors(['room_code']);
});

it('can access session lobby page', function (): void {
    $user = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'room_code' => 'ABC123',
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
    ]);
    $response = $this->actingAs($user)->get(route(
        'sessions.lobby',
        $session->room_code,
    ));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('sessions/lobby'));
});

it('displays participants in session lobby', function (): void {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'room_code' => 'ABC123',
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
    ]);
    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $response = $this->actingAs($host)->get(route(
        'sessions.lobby',
        $session->room_code,
    ));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page->component('sessions/lobby')->has(
                'session.participants',
                1,
            ),
        );
});

it('can access session play page', function (): void {
    $user = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'room_code' => 'ABC123',
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
    ]);
    $response = $this->actingAs($user)->get(route(
        'sessions.play',
        $session->room_code,
    ));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('sessions/play'));
});

it('can access session results page', function (): void {
    $user = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'room_code' => 'ABC123',
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
    ]);
    $response = $this->actingAs($user)->get(route(
        'sessions.results',
        $session->room_code,
    ));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('sessions/results'));
});
