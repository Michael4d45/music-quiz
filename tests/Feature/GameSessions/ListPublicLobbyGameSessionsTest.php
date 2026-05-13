<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\User;

test('lobby is available for guest session without prior login', function (): void {
    $response = $this->withHeader('Origin', rtrim(
        (string) config('app.url'),
        '/',
    ))->getJson('/api/game-sessions/lobby');

    $response->assertSuccessful();
    $response->assertJsonStructure(['sessions']);
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
