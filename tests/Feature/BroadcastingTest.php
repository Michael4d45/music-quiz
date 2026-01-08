<?php

declare(strict_types=1);

use App\Events\TimerUpdated;
use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('participant is added to session when joining', function (): void {
    $user = User::factory()->create();
    $host = User::factory()->create();

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    // Create session
    $this->actingAs($host)->post(route('sessions.store'), [
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'max_players' => 6,
    ]);

    $session = GameSession::where('host_id', $host->id)->latest()->first();

    // Join session
    $this->actingAs($user)->post(route('sessions.join.store'), [
        'room_code' => $session->room_code,
        'guest_name' => 'Test User',
    ]);

    // Verify participant was added
    $participant = SessionParticipant::where('session_id', $session->id)
        ->where('user_id', $user->id)
        ->first();

    expect($participant)->not->toBeNull();
    expect($participant->guest_name)->toBe('Test User');
});

it('participant is removed from session when leaving', function (): void {
    $user = User::factory()->create();
    $host = User::factory()->create();

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    // Create session
    $this->actingAs($host)->post(route('sessions.store'), [
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'max_players' => 6,
    ]);

    $session = GameSession::where('host_id', $host->id)->latest()->first();

    // Join session
    $this->actingAs($user)->post(route('sessions.join.store'), [
        'room_code' => $session->room_code,
        'guest_name' => 'Test User',
    ]);

    // Verify both participants exist
    $countBefore = SessionParticipant::where(
        'session_id',
        $session->id,
    )->count();
    expect($countBefore)->toBe(2);

    // Leave session
    $response = $this->actingAs($user)->post(route(
        'sessions.leave',
        $session->room_code,
    ));
    $response->assertRedirect(route('active-games.index'));

    // Verify participant was removed (should be 1 left - the host)
    $countAfter = SessionParticipant::where(
        'session_id',
        $session->id,
    )->count();
    expect($countAfter)->toBe(1);
    expect(
        SessionParticipant::where('session_id', $session->id)
            ->where('user_id', $user->id)
            ->count(),
    )
        ->toBe(0);
    expect(
        SessionParticipant::where('session_id', $session->id)
            ->where('user_id', $host->id)
            ->count(),
    )
        ->toBe(1);
});

it('creates timer updated event correctly', function (): void {
    $host = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    // Create session
    $this->actingAs($host)->post(route('sessions.store'), [
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'max_players' => 6,
    ]);

    $session = GameSession::where('host_id', $host->id)->latest()->first();

    // Create timer event
    $event = new TimerUpdated(
        session: $session,
        remainingSeconds: 30,
        status: 'running',
    );

    // Verify event properties
    expect($event->session)->toBe($session);
    expect($event->remainingSeconds)->toBe(30);
    expect($event->status)->toBe('running');
    expect($event->broadcastOn()[0]->name)
        ->toBe("presence-session.{$session->room_code}");
    expect($event->broadcastAs())->toBe('timer.updated');
});

it('channel authorization logic works correctly', function (): void {
    $user = User::factory()->create();
    $host = User::factory()->create();
    $otherUser = User::factory()->create();

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    // Create session
    $this->actingAs($host)->post(route('sessions.store'), [
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'max_players' => 6,
    ]);

    $session = GameSession::where('host_id', $host->id)->latest()->first();

    // User joins session
    $this->actingAs($user)->post(route('sessions.join.store'), [
        'room_code' => $session->room_code,
        'guest_name' => 'Test User',
    ]);

    // Test authorization logic directly
    $authorizationCallback = function ($authUser, $roomCode) {
        return GameSession::where('room_code', $roomCode)
            ->first()
            ?->participants()
            ->where('user_id', $authUser->id)
            ->exists();
    };

    // Simulate channel authorization for participant
    $canAccess = $authorizationCallback($user, $session->room_code);
    expect($canAccess)->toBeTrue();

    // Simulate channel authorization for non-participant
    $canAccessOther = $authorizationCallback($otherUser, $session->room_code);
    expect($canAccessOther)->toBeFalse();
});
