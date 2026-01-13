<?php

declare(strict_types=1);

use App\Models\User;

it('can view active games page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $logPath = setup_log_capture('active-games.log');

    $page = visit_with_error_init('/active-games')
        ->assertNoJavaScriptErrors()
        ->waitForText('Active Games', 5)
        ->assertSee('Active Games')
        ->assertSee('Create Game')
        ->screenshot(filename: 'active-games.png');

    assert_no_log_errors($logPath);
});

it('can view create session page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit('/sessions/create')
        ->assertNoJavaScriptErrors()
        ->waitForText('Create New Game', 5)
        ->assertSee('Create New Game')
        ->assertSee('Max Players')
        ->assertSee('Create Game');
});

it('can play a game and submit an answer', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $quizMode = \App\Models\QuizMode::factory()->create();
    $scoringRule = \App\Models\ScoringRule::factory()->create();
    $playlist = \App\Models\Playlist::factory()->create([
        'user_id' => $user->id,
    ]);
    $question = \App\Models\QuizQuestion::factory()->create([
        'correct_answer' => 'Test Answer',
    ]);

    \App\Models\PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
    ]);

    $session = \App\Models\GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => $playlist->id,
        'status' => \App\Enums\SessionStatus::InProgress,
    ]);

    $round = \App\Models\SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
    ]);

    \App\Models\SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    visit("/sessions/{$session->room_code}/play")
        ->assertNoJavaScriptErrors()
        ->waitForText('Round 1', 5)
        ->type('input[placeholder="Type your answer here..."]', 'Test Answer')
        ->press('Submit Answer')
        ->waitForText('Correct!', 5)
        ->assertSee('+')
        ->assertSee('points')
        ->screenshot(filename: 'game-play.png');
});
