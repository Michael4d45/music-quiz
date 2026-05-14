<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\MultipleChoiceOption;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
use App\Models\User;

test('host can start a lobby session and first round is active', function (): void {
    $host = User::factory()->create();
    $playlist = Playlist::factory()->for($host)->create();

    $question = QuizQuestion::factory()->create([
        'user_id' => $host->id,
        'question_type' => QuestionType::MultipleChoice,
        'correct_answer' => 'n/a',
        'base_points' => 50,
    ]);

    MultipleChoiceOption::factory()->create([
        'question_id' => $question->id,
        'option_text' => 'Wrong',
        'is_correct' => false,
        'sort_order' => 1,
    ]);

    MultipleChoiceOption::factory()->create([
        'question_id' => $question->id,
        'option_text' => 'Right',
        'is_correct' => true,
        'sort_order' => 2,
    ]);

    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'playlist_id' => $playlist->id,
        'status' => SessionStatus::Lobby,
    ]);

    $response = $this->actingAs($host, 'web')->postJson(
        "/api/game-sessions/{$session->id}/start",
    );

    $response->assertSuccessful();
    $session->refresh();

    expect($session->status)->toBe(SessionStatus::InProgress);
    expect(SessionRound::query()->where('session_id', $session->id)->count())->toBe(1);
    expect(
        SessionParticipant::query()
            ->where('session_id', $session->id)
            ->where('user_id', $host->id)
            ->count(),
    )->toBe(1);

    $round = SessionRound::query()
        ->where('session_id', $session->id)
        ->where('round_number', 1)
        ->firstOrFail();

    expect($round->started_at)->not->toBeNull();
    expect($round->ended_at)->toBeNull();

    $response->assertJsonPath('rounds.0.question.multiple_choice_options.1.is_correct', true);
});

test('participant can submit a multiple choice answer and score updates', function (): void {
    $host = User::factory()->create();
    $player = User::factory()->create();
    $playlist = Playlist::factory()->for($host)->create();

    $question = QuizQuestion::factory()->create([
        'user_id' => $host->id,
        'question_type' => QuestionType::MultipleChoice,
        'correct_answer' => 'n/a',
        'base_points' => 75,
    ]);

    MultipleChoiceOption::factory()->create([
        'question_id' => $question->id,
        'option_text' => 'Wrong',
        'is_correct' => false,
        'sort_order' => 1,
    ]);

    $correct = MultipleChoiceOption::factory()->create([
        'question_id' => $question->id,
        'option_text' => 'Right',
        'is_correct' => true,
        'sort_order' => 2,
    ]);

    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'playlist_id' => $playlist->id,
        'status' => SessionStatus::Lobby,
    ]);

    SessionParticipant::factory()->create([
        'session_id' => $session->id,
        'user_id' => $player->id,
    ]);

    $this->actingAs($host, 'web')->postJson(
        "/api/game-sessions/{$session->id}/start",
    )->assertSuccessful();

    $round = SessionRound::query()
        ->where('session_id', $session->id)
        ->where('round_number', 1)
        ->firstOrFail();

    $participant = SessionParticipant::query()
        ->where('session_id', $session->id)
        ->where('user_id', $player->id)
        ->firstOrFail();

    $answerResponse = $this->actingAs($player, 'web')->postJson(
        "/api/game-sessions/{$session->id}/rounds/{$round->id}/answer",
        [
            'selected_option_id' => $correct->id,
        ],
    );

    $answerResponse->assertSuccessful();
    $answerResponse->assertJsonPath(
        'rounds.0.answers.0.points_awarded',
        75,
    );

    $participant->refresh();
    expect($participant->current_total_score)->toBe(75);
});

test('host can advance rounds until session completes', function (): void {
    $host = User::factory()->create();
    $playlist = Playlist::factory()->for($host)->create();

    foreach ([100, 200] as $sortOrder) {
        $question = QuizQuestion::factory()->create([
            'user_id' => $host->id,
            'question_type' => QuestionType::Title,
            'correct_answer' => 'Answer ' . $sortOrder,
            'base_points' => 10,
        ]);

        PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => $sortOrder,
        ]);
    }

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'playlist_id' => $playlist->id,
        'status' => SessionStatus::Lobby,
    ]);

    $this->actingAs($host, 'web')->postJson(
        "/api/game-sessions/{$session->id}/start",
    )->assertSuccessful();

    $this->actingAs($host, 'web')->postJson(
        "/api/game-sessions/{$session->id}/advance-round",
    )->assertSuccessful();

    $session->refresh();
    expect($session->status)->toBe(SessionStatus::InProgress);

    $round1 = SessionRound::query()
        ->where('session_id', $session->id)
        ->where('round_number', 1)
        ->firstOrFail();
    expect($round1->ended_at)->not->toBeNull();

    $round2 = SessionRound::query()
        ->where('session_id', $session->id)
        ->where('round_number', 2)
        ->firstOrFail();
    expect($round2->started_at)->not->toBeNull();

    $this->actingAs($host, 'web')->postJson(
        "/api/game-sessions/{$session->id}/advance-round",
    )->assertSuccessful();

    $session->refresh();
    expect($session->status)->toBe(SessionStatus::Completed);
    expect($session->ended_at)->not->toBeNull();
});

test('non-host cannot start the game', function (): void {
    $host = User::factory()->create();
    $other = User::factory()->create();
    $playlist = Playlist::factory()->for($host)->create();

    $question = QuizQuestion::factory()->create([
        'user_id' => $host->id,
        'question_type' => QuestionType::Title,
        'correct_answer' => 'X',
    ]);

    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $session = GameSession::factory()->create([
        'host_id' => $host->id,
        'playlist_id' => $playlist->id,
        'status' => SessionStatus::Lobby,
    ]);

    $this->actingAs($other, 'web')->postJson(
        "/api/game-sessions/{$session->id}/start",
    )->assertForbidden();
});
