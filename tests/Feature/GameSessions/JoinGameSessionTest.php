<?php

declare(strict_types=1);

use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Models\User;

test('guest can join a lobby session by room code', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $response = $this->actingAs(
        $guest,
        'web',
    )->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('session_id', $session->id);
    expect(
        SessionParticipant::query()
            ->where('session_id', $session->id)
            ->where('user_id', $guest->id)
            ->count(),
    )
        ->toBe(1);
});

test('join is idempotent for the same user', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this
        ->actingAs($guest, 'web')
        ->postJson('/api/game-sessions/join', [
            'room_code' => $session->room_code,
        ])
        ->assertSuccessful();

    $this
        ->actingAs($guest, 'web')
        ->postJson('/api/game-sessions/join', [
            'room_code' => $session->room_code,
        ])
        ->assertSuccessful();

    expect(
        SessionParticipant::query()
            ->where('session_id', $session->id)
            ->where('user_id', $guest->id)
            ->count(),
    )
        ->toBe(1);
});

test('guest cannot join a second lobby while still a participant in another active session', function (): void {
    $guest = User::factory()->guest()->create();
    $sessionA = GameSession::factory()->publicLobby()->create();
    $sessionB = GameSession::factory()->publicLobby()->create();

    SessionParticipant::factory()->create([
        'session_id' => $sessionA->id,
        'user_id' => $guest->id,
    ]);

    $response = $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $sessionB->room_code,
    ]);

    $response->assertStatus(422);
});

test('registered user may join a new lobby while still a participant elsewhere', function (): void {
    $user = User::factory()->create();
    $sessionA = GameSession::factory()->publicLobby()->create();
    $sessionB = GameSession::factory()->publicLobby()->create();

    SessionParticipant::factory()->create([
        'session_id' => $sessionA->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $sessionB->room_code,
    ]);

    $response->assertSuccessful();
});

test('guest may rejoin the same lobby they already participate in when the room is full', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()
        ->publicLobby()
        ->create([
            'max_players' => 2,
        ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $guest->id,
    ]);

    $other = User::factory()->guest()->create();
    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $other->id,
    ]);

    $response = $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ]);

    $response->assertSuccessful();
});

test('join rejects when room is full', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()
        ->publicLobby()
        ->create([
            'max_players' => 1,
        ]);

    $other = User::factory()->guest()->create();
    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $other->id,
    ]);

    $response = $this->actingAs(
        $guest,
        'web',
    )->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ]);

    $response->assertStatus(422);
});

test('guest can load a lobby session by room code', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $response = $this->actingAs(
        $guest,
        'web',
    )->getJson('/api/game-sessions/room/' . $session->room_code);

    $response->assertSuccessful();
    $response->assertJsonPath('id', $session->id);
});

test('guest can leave a session they joined', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this
        ->actingAs($guest, 'web')
        ->postJson('/api/game-sessions/join', [
            'room_code' => $session->room_code,
        ])
        ->assertSuccessful();

    $response = $this->actingAs($guest, 'web')->deleteJson(
        '/api/game-sessions/' . $session->id . '/leave',
    );

    $response->assertSuccessful();
    expect(
        SessionParticipant::query()
            ->where('session_id', $session->id)
            ->where('user_id', $guest->id)
            ->count(),
    )
        ->toBe(0);
});
