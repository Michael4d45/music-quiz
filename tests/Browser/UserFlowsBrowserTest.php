<?php

declare(strict_types=1);

use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;

beforeEach(function (): void {
    setup_log_capture('user-flows.log');
});

afterEach(function (): void {
    assert_no_log_errors(storage_path('logs/user-flows.log'));
});

it('guest can open game lobby and see joinable games UI', function (): void {
    visit('/game-sessions/lobby')
        ->assertNoJavaScriptErrors()
        ->waitForText('Game lobby', 15)
        ->assertSee('Join by room code')
        ->assertSee('No joinable public games right now.');
});

it('guest can see a public lobby game and join from the card', function (): void {
    $host = User::factory()->create();
    $session = GameSession::factory()
        ->for($host, 'host')
        ->publicLobby()
        ->create(['max_players' => 10]);

    $code = $session->room_code;

    visit('/game-sessions/lobby')
        ->assertNoJavaScriptErrors()
        ->waitForText($code, 15)
        ->assertSee('Join this game')
        ->click('Join this game')
        ->waitForText('Room ' . $code, 15)
        ->assertPathIs('/game-sessions/room/' . $code)
        ->assertNoJavaScriptErrors();
});

it('guest can join using the room code field', function (): void {
    $host = User::factory()->create();
    $session = GameSession::factory()
        ->for($host, 'host')
        ->publicLobby()
        ->create(['max_players' => 10]);

    $code = $session->room_code;

    visit('/game-sessions/lobby')
        ->assertNoJavaScriptErrors()
        ->waitForText('Join by room code', 15)
        ->type('#join-code', $code)
        ->click('Join')
        ->waitForText('Room ' . $code, 15)
        ->assertPathIs('/game-sessions/room/' . $code)
        ->assertNoJavaScriptErrors();
});

it('registered user can host a game session from my sessions', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $mode = QuizMode::factory()->create(['name' => 'Browser Flow Mode']);
    $rule = ScoringRule::factory()->create(['name' => 'Browser Flow Rule']);

    visit('/my/game-sessions')
        ->assertNoJavaScriptErrors()
        ->waitForText('My game sessions', 15)
        ->select('#host-quiz-mode', $mode->id)
        ->select('#host-scoring-rule', $rule->id)
        ->click('Create session')
        ->waitForText('Room ', 15)
        ->assertNoJavaScriptErrors();
});

it('registered user can open playlist and question management pages', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit('/my/playlists')
        ->assertNoJavaScriptErrors()
        ->waitForText('My playlists', 15)
        ->assertSee('More about this screen');

    visit('/my/quiz-questions')
        ->assertNoJavaScriptErrors()
        ->waitForText('My quiz questions', 15);

    visit('/my/music-tracks')
        ->assertNoJavaScriptErrors()
        ->waitForText('My music tracks', 15)
        ->assertNoJavaScriptErrors();
});
