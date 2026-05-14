<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Models\User;
use App\Support\GameSessions\ValidGameRoomCode;

test('short room code returns 422 with a clear message', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'web')->getJson(
        '/api/game-sessions/room/SOL01',
    );

    $response->assertStatus(422);
    $response->assertJsonPath('message', ValidGameRoomCode::invalidFormatMessage());
});

test('authenticated user may load recap for a session they host', function (): void {
    $host = User::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::Completed,
    ]);

    $response = $this->actingAs($host, 'web')->getJson(
        "/api/game-sessions/{$session->id}/recap",
    );

    $response->assertSuccessful();
    $response->assertJsonPath('session.id', $session->id);
});

test('recap is forbidden for users who did not participate', function (): void {
    $host = User::factory()->create();
    $stranger = User::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::Completed,
    ]);

    $response = $this->actingAs($stranger, 'web')->getJson(
        "/api/game-sessions/{$session->id}/recap",
    );

    $response->assertForbidden();
});

test('participant may load recap for a completed session they played in', function (): void {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::Completed,
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $response = $this->actingAs($player, 'web')->getJson(
        "/api/game-sessions/{$session->id}/recap",
    );

    $response->assertSuccessful();
    $response->assertJsonPath('session.id', $session->id);
});

test('my game sessions includes games joined as a player', function (): void {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'status' => SessionStatus::Lobby,
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $response = $this->actingAs($player, 'web')->getJson('/api/my/game-sessions');

    $response->assertSuccessful();
    $ids = collect($response->json('sessions'))->pluck('id')->all();
    expect($ids)->toContain($session->id);
});
