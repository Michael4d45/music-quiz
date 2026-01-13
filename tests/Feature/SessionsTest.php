<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;

it('returns active games', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->getJson('/api/sessions/active-games');

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'sessions' => [
                '*' => [
                    'id',
                    'room_code',
                    'status',
                    'max_players',
                ],
            ],
        ]);
});

it('creates a new game session', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $sessionData = [
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'max_players' => 5,
    ];

    $response = $this->withToken($token)->postJson(
        '/api/sessions',
        $sessionData,
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'session' => [
                'id',
                'room_code',
                'host_id',
                'status',
                'max_players',
            ],
        ]);

    $this->assertDatabaseHas('game_sessions', [
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'max_players' => 5,
    ]);
});

it('joins a game session', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::Lobby,
    ]);

    $joinData = [
        'room_code' => $session->room_code,
        'guest_name' => null,
    ];

    $response = $this->withToken($token)->postJson(
        '/api/sessions/join',
        $joinData,
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'session' => [
                'id',
                'room_code',
                'participants' => [
                    '*' => [
                        'user_id',
                        'is_connected',
                    ],
                ],
            ],
        ]);
});

it('returns session lobby details', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->getJson(
        "/api/sessions/{$session->room_code}",
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'session' => [
                'id',
                'room_code',
                'status',
                'participants',
            ],
        ]);
});

it('starts a game session', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/start",
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'session' => [
                'id',
                'status',
            ],
        ]);
});

it('leaves a game session', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/leave",
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'message',
        ]);
});

it('returns session play data', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
    ]);

    $response = $this->withToken($token)->getJson(
        "/api/sessions/{$session->room_code}/play",
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'round',
            'question',
            'participant',
        ]);
});

it('returns session results', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::Completed,
    ]);

    $response = $this->withToken($token)->getJson(
        "/api/sessions/{$session->room_code}/results",
    );

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'final_scores' => [
                '*' => [
                    'participant' => [
                        'user' => [
                            'id',
                            'name',
                        ],
                    ],
                    'total_points',
                    'position',
                ],
            ],
            'rounds',
        ]);
});

it('returns 404 for non-existent session', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/sessions/NONEXISTENT');

    $response->assertNotFound();
});

it('returns unauthorized when not authenticated', function () {
    $response = $this->getJson('/api/sessions/active-games');

    $response->assertUnauthorized();
});
