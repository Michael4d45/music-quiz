<?php

declare(strict_types=1);

use App\Models\MusicTrack;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;
use App\Enums\QuestionType;

it('returns user quiz questions', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $question = \App\Models\QuizQuestion::factory()->create(['user_id' => $user->id]);

    $response = $this->withToken($token)->getJson('/api/quiz-questions');

    $response->assertSuccessful()->assertJsonStructure([
        'quiz_questions' => [
            'data' => [
                '*' => [
                    'id',
                    'question_type',
                    'correct_answer',
                    'user_id'
                ]
            ]
        ]
    ]);
});

it('creates a new quiz question', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $track = MusicTrack::factory()->create();

    $questionData = [
        'question_type' => QuestionType::Artist->value,
        'correct_answer' => 'Test Artist',
        'track_id' => $track->id,
        'prompt_text' => 'Who is the artist of this song?',
        'base_points' => 1000,
        'difficulty_level' => 2
    ];

    $response = $this->withToken($token)->postJson('/api/quiz-questions', $questionData);

    $response->assertSuccessful()->assertJsonStructure([
        'quiz_question' => [
            'id',
            'question_type',
            'correct_answer',
            'user_id'
        ]
    ]);

    $this->assertDatabaseHas('quiz_questions', [
        'correct_answer' => 'Test Artist',
        'user_id' => $user->id
    ]);
});

it('validates required fields when creating question', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/quiz-questions', []);

    $response->assertUnprocessable()->assertJsonStructure([
        'message',
        'errors' => [
            'question_type',
            'correct_answer'
        ]
    ]);
});

it('returns unauthorized when not authenticated', function () {
    $response = $this->getJson('/api/quiz-questions');

    $response->assertUnauthorized();
});

it('returns quiz modes', function () {
    $quizMode = QuizMode::factory()->create();

    $response = $this->getJson('/api/quiz-modes');

    $response->assertSuccessful()->assertJsonStructure([
        'quiz_modes' => [
            '*' => [
                'id',
                'name'
            ]
        ]
    ]);
});

it('returns scoring rules', function () {
    $scoringRule = ScoringRule::factory()->create();

    $response = $this->getJson('/api/scoring-rules');

    $response->assertSuccessful()->assertJsonStructure([
        'scoring_rules' => [
            '*' => [
                'id',
                'name'
            ]
        ]
    ]);
});