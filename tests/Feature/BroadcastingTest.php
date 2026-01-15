<?php

declare(strict_types=1);

use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\SessionParticipant;
use App\Models\User;

beforeEach(function () {
    config()->set('broadcasting.default', 'pusher');
    config()->set('broadcasting.connections.pusher.key', 'test-key');
    config()->set('broadcasting.connections.pusher.secret', 'test-secret');
    config()->set('broadcasting.connections.pusher.app_id', 'test-app');
    config()->set('broadcasting.connections.pusher.options.cluster', 'mt1');

    // Re-load channels since they might have been loaded with the 'null' driver during boot
    require base_path('routes/channels.php');
});

it('authenticates session presence channel for participants', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $session = GameSession::factory()->create([
        'host_id' => User::factory()->create()->id,
        'quiz_mode_id' => QuizMode::factory()->create()->id,
        'scoring_rule_id' => ScoringRule::factory()->create()->id,
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    $broadcastData = [
        'socket_id' => '123.456',
        'channel_name' => 'presence-session.' . $session->room_code,
    ];

    $response = $this->withToken($token)->postJson(
        '/api/broadcasting/auth',
        $broadcastData,
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'auth',
        ]);
});

it('forbids session presence channel for non-participants', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $session = GameSession::factory()->create([
        'host_id' => User::factory()->create()->id,
        'quiz_mode_id' => QuizMode::factory()->create()->id,
        'scoring_rule_id' => ScoringRule::factory()->create()->id,
    ]);

    $broadcastData = [
        'socket_id' => '123.456',
        'channel_name' => 'presence-session.' . $session->room_code,
    ];

    $response = $this->withToken($token)->postJson(
        '/api/broadcasting/auth',
        $broadcastData,
    );

    $response->assertForbidden();
});

it('returns unauthorized when not authenticated', function () {
    $broadcastData = [
        'socket_id' => '123.456',
        'channel_name' => 'presence-session.ABCD1234',
    ];

    $response = $this->postJson('/api/broadcasting/auth', $broadcastData);

    $response->assertUnauthorized();
});
