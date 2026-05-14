<?php

declare(strict_types=1);

use App\Events\GameSessionParticipantJoined;
use App\Events\GameSessionUpdated;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

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
    $response->assertJsonPath('session.id', $session->id);
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

test('join stores optional display_name on participant guest_name column', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
        'display_name' => '  Luna  ',
    ])->assertSuccessful();

    $participant = SessionParticipant::query()
        ->where('session_id', $session->id)
        ->where('user_id', $guest->id)
        ->first();

    expect($participant)->not->toBeNull();
    expect($participant->guest_name)->toBe('Luna');
});

test('join broadcasts participant joined for a new seat', function (): void {
    Event::fake([GameSessionParticipantJoined::class]);
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ])->assertSuccessful();

    Event::assertDispatched(GameSessionParticipantJoined::class);
});

test('join idempotent does not broadcast participant joined twice', function (): void {
    Event::fake([GameSessionParticipantJoined::class]);
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ])->assertSuccessful();

    $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ])->assertSuccessful();

    Event::assertDispatchedTimes(GameSessionParticipantJoined::class, 1);
});

test('leave broadcasts session updated', function (): void {
    $guest = User::factory()->guest()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this->actingAs($guest, 'web')->postJson('/api/game-sessions/join', [
        'room_code' => $session->room_code,
    ])->assertSuccessful();

    Event::fake([GameSessionUpdated::class]);

    $this->actingAs($guest, 'web')
        ->deleteJson('/api/game-sessions/' . $session->id . '/leave')
        ->assertSuccessful();

    Event::assertDispatched(GameSessionUpdated::class);
});

test('join rejects display_name longer than 64 characters', function (): void {
    $user = User::factory()->create();
    $session = GameSession::factory()->publicLobby()->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/game-sessions/join', [
            'room_code' => $session->room_code,
            'display_name' => str_repeat('a', 65),
        ])
        ->assertUnprocessable();
});
