<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\AnswerVariant;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizMode;
use App\Models\QuizQuestion;
use App\Models\ScoringRule;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
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
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    // Add a question to the playlist
    $question = QuizQuestion::factory()->create();
    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => $playlist->id,
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

it('creates rounds from playlist when game starts', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    // Create 3 questions in the playlist
    $questions = QuizQuestion::factory()->count(3)->create();
    foreach ($questions as $index => $question) {
        PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => $index + 1,
        ]);
    }

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => $playlist->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/start",
    );

    $response->assertSuccessful();

    // Assert rounds were created from playlist
    $this->assertDatabaseCount('session_rounds', 3);

    foreach ($questions as $index => $question) {
        $this->assertDatabaseHas('session_rounds', [
            'session_id' => $session->id,
            'question_id' => $question->id,
            'round_number' => $index + 1,
        ]);
    }

    // Assert first round is started
    $session->refresh();
    $firstRound = $session->rounds()->where('round_number', 1)->first();
    expect($firstRound->started_at)->not->toBeNull();
});

it('prevents starting game without a playlist', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => null,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/start",
    );

    $response->assertStatus(422);
    $response->assertJsonPath(
        'message',
        'Cannot start game without a playlist',
    );
});

it('prevents starting game with empty playlist', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $user->id]);

    $session = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'playlist_id' => $playlist->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/start",
    );

    $response->assertStatus(422);
    $response->assertJsonPath(
        'message',
        'Cannot start game with empty playlist',
    );
});

it('submits a correct answer and awards points', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create([
        'base_points' => 1000,
        'decay_factor' => 0.5,
        'max_time_ms' => 30000,
        'streak_bonus_enabled' => false,
    ]);

    $question = QuizQuestion::factory()->create([
        'correct_answer' => 'The Beatles',
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
        'current_total_score' => 0,
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/answer",
        ['answer' => 'The Beatles'],
    );

    $response->assertSuccessful();
    $response->assertJsonPath('is_correct', true);
    $response->assertJsonStructure([
        'is_correct',
        'points_awarded',
        'correct_answer',
    ]);

    $this->assertDatabaseHas('player_answers', [
        'round_id' => $round->id,
        'participant_id' => $participant->id,
        'is_correct' => true,
    ]);
});

it('submits an incorrect answer and awards no points', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create(['base_points' => 1000]);

    $question = QuizQuestion::factory()->create([
        'correct_answer' => 'The Beatles',
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
        'current_total_score' => 0,
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/answer",
        ['answer' => 'Wrong Answer'],
    );

    $response->assertSuccessful();
    $response->assertJsonPath('is_correct', false);
    $response->assertJsonPath('points_awarded', 0);

    $this->assertDatabaseHas('player_answers', [
        'round_id' => $round->id,
        'participant_id' => $participant->id,
        'is_correct' => false,
        'points_awarded' => 0,
    ]);
});

it('accepts answer variants as correct', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create(['base_points' => 1000]);

    $question = QuizQuestion::factory()->create([
        'correct_answer' => 'The Beatles',
    ]);

    // Add answer variant
    AnswerVariant::factory()->create([
        'question_id' => $question->id,
        'accepted_text' => 'Beatles',
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/answer",
        ['answer' => 'Beatles'],
    );

    $response->assertSuccessful();
    $response->assertJsonPath('is_correct', true);
});

it('prevents duplicate answer submission', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $question = QuizQuestion::factory()->create([
        'correct_answer' => 'The Beatles',
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
    ]);

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $round = SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
    ]);

    // First answer
    $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/answer",
        ['answer' => 'The Beatles'],
    );

    // Second answer should fail
    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/answer",
        ['answer' => 'Different Answer'],
    );

    $response->assertStatus(409);
    $response->assertJsonPath('message', 'Already answered this round');
});

it('updates participant total score after correct answer', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create([
        'base_points' => 1000,
        'decay_factor' => null,
        'max_time_ms' => null,
    ]);

    $question = QuizQuestion::factory()->create([
        'correct_answer' => 'The Beatles',
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
        'started_at' => now(),
    ]);

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
        'current_total_score' => 500,
    ]);

    SessionRound::factory()->create([
        'session_id' => $session->id,
        'question_id' => $question->id,
        'round_number' => 1,
        'started_at' => now(),
    ]);

    $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/answer",
        ['answer' => 'The Beatles'],
    );

    $participant->refresh();
    expect($participant->current_total_score)->toBe(1500);
});

it('advances to the next round', function () {
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

    // Create 2 rounds
    SessionRound::factory()->create([
        'session_id' => $session->id,
        'round_number' => 1,
        'started_at' => now()->subMinutes(1),
        'ended_at' => now()->subSeconds(30),
    ]);

    $round2 = SessionRound::factory()->create([
        'session_id' => $session->id,
        'round_number' => 2,
        'started_at' => null,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/next-round",
    );

    $response->assertSuccessful();

    $round2->refresh();
    expect($round2->started_at)->not->toBeNull();
});

it('ends game when no more rounds', function () {
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

    // Create only 1 round and end it
    SessionRound::factory()->create([
        'session_id' => $session->id,
        'round_number' => 1,
        'started_at' => now()->subMinutes(1),
        'ended_at' => now()->subSeconds(30),
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/next-round",
    );

    $response->assertSuccessful();

    $session->refresh();
    expect($session->status)->toBe(SessionStatus::Completed);
    expect($session->ended_at)->not->toBeNull();
});

it('prevents non-host from advancing round', function () {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $token = $player->createToken('test-token')->plainTextToken;

    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
        'status' => SessionStatus::InProgress,
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/next-round",
    );

    $response->assertStatus(403);
});

it('allows unauthenticated guest to join session', function () {
    $host = User::factory()->create();
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
        'guest_name' => 'GuestPlayer',
    ];

    $response = $this->postJson('/api/sessions/join', $joinData);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'session',
        'token',
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'GuestPlayer',
        'is_guest' => true,
    ]);

    $this->assertDatabaseHas('session_participants', [
        'session_id' => $session->id,
        'guest_name' => 'GuestPlayer',
    ]);
});

it('generates final scores when game ends', function () {
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

    // Create 2 participants with different scores
    $p1 = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'current_total_score' => 1000,
        'user_id' => User::factory()->create()->id,
    ]);
    $p2 = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'current_total_score' => 2000,
        'user_id' => User::factory()->create()->id,
    ]);

    // Create only 1 round and it has ended
    SessionRound::factory()->create([
        'session_id' => $session->id,
        'round_number' => 1,
        'started_at' => now()->subMinutes(1),
        'ended_at' => now()->subSeconds(30),
    ]);

    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/next-round",
    );

    $response->assertSuccessful();

    $this->assertDatabaseHas('session_final_scores', [
        'session_id' => $session->id,
        'participant_id' => $p2->id,
        'final_score' => 2000,
        'final_rank' => 1,
    ]);

    $this->assertDatabaseHas('session_final_scores', [
        'session_id' => $session->id,
        'participant_id' => $p1->id,
        'final_score' => 1000,
        'final_rank' => 2,
    ]);
});

it('calculates streaks correctly when game ends', function () {
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

    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
    ]);

    // Create 4 rounds, all ended
    $rounds = [];
    for ($i = 1; $i <= 4; $i++) {
        $rounds[] = SessionRound::factory()->create([
            'session_id' => $session->id,
            'round_number' => $i,
            'started_at' => now()->subMinutes(10),
            'ended_at' => now()->subMinutes(5),
        ]);
    }

    // Round 1: Correct
    PlayerAnswer::factory()->create([
        'round_id' => $rounds[0]->id,
        'participant_id' => $participant->id,
        'is_correct' => true,
    ]);
    // Round 2: Correct
    PlayerAnswer::factory()->create([
        'round_id' => $rounds[1]->id,
        'participant_id' => $participant->id,
        'is_correct' => true,
    ]);
    // Round 3: Incorrect
    PlayerAnswer::factory()->create([
        'round_id' => $rounds[2]->id,
        'participant_id' => $participant->id,
        'is_correct' => false,
    ]);
    // Round 4: Correct
    PlayerAnswer::factory()->create([
        'round_id' => $rounds[3]->id,
        'participant_id' => $participant->id,
        'is_correct' => true,
    ]);

    // Calling next-round when all are ended and no more rounds exist should complete
    $response = $this->withToken($token)->postJson(
        "/api/sessions/{$session->room_code}/next-round",
    );

    $response->assertSuccessful();
    $response->assertJsonPath('status', 'Completed');

    $session->refresh();
    expect($session->status)->toBe(SessionStatus::Completed);

    $this->assertDatabaseHas('session_final_scores', [
        'session_id' => $session->id,
        'participant_id' => $participant->id,
        'longest_streak' => 2,
    ]);
});
