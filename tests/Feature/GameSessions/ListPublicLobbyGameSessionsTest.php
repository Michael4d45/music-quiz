<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Models\User;

test('lobby is available without authentication, session, or spa origin headers', function (): void {
    $response = $this->getJson('/api/game-sessions/lobby');

    $response->assertSuccessful();
    $response->assertJsonStructure(['sessions', 'current_session']);
});

test('lobby returns public sessions in lobby status that have not started', function (): void {
    $user = User::factory()->create();

    $visible = GameSession::factory()->publicLobby()->create();

    GameSession::factory()->create([
        'is_public' => true,
        'status' => SessionStatus::InProgress,
        'started_at' => now()->subMinutes(5),
    ]);

    GameSession::factory()->create([
        'is_public' => false,
        'status' => SessionStatus::Lobby,
        'started_at' => null,
    ]);

    GameSession::factory()->create([
        'is_public' => true,
        'status' => SessionStatus::Lobby,
        'started_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/game-sessions/lobby');

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'sessions');
    $response->assertJsonPath('sessions.0.id', $visible->id);
    $response->assertJsonPath('sessions.0.room_code', $visible->room_code);
    $response->assertJsonPath('sessions.0.participant_count', 0);
});

test('lobby includes current_session when the user is a participant in a non-completed game', function (): void {
    $user = User::factory()->create();
    $session = GameSession::factory()->create([
        'is_public' => false,
        'status' => SessionStatus::Lobby,
        'started_at' => null,
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'web')->getJson('/api/game-sessions/lobby');

    $response->assertSuccessful();
    $response->assertJsonPath('current_session.room_code', $session->room_code);
    $response->assertJsonPath('current_session.is_public', false);
    $response->assertJsonCount(0, 'sessions');
});

test('lobby includes current_session when the user hosts an active session', function (): void {
    $user = User::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'status' => SessionStatus::Lobby,
        'started_at' => null,
        'is_public' => false,
    ]);

    $response = $this->actingAs($user, 'web')->getJson('/api/game-sessions/lobby');

    $response->assertSuccessful();
    $response->assertJsonPath('current_session.id', $session->id);
});
