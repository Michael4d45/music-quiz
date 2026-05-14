<?php

declare(strict_types=1);

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;

test('guest cannot create a hosted game session', function (): void {
    $guest = User::factory()->guest()->create();
    $mode = QuizMode::factory()->create();
    $rule = ScoringRule::factory()->create();

    $response = $this->actingAs($guest, 'web')->postJson('/api/my/game-sessions', [
        'quiz_mode_id' => $mode->id,
        'scoring_rule_id' => $rule->id,
        'playlist_id' => null,
        'max_players' => 8,
        'is_public' => true,
    ]);

    $response->assertForbidden();
});

test('registered user can create a second hosted game while the first is still active', function (): void {
    $user = User::factory()->create();
    $mode = QuizMode::factory()->create();
    $rule = ScoringRule::factory()->create();

    GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $mode->id,
        'scoring_rule_id' => $rule->id,
    ]);

    $response = $this->actingAs($user, 'web')->postJson('/api/my/game-sessions', [
        'quiz_mode_id' => $mode->id,
        'scoring_rule_id' => $rule->id,
        'playlist_id' => null,
        'max_players' => 8,
        'is_public' => true,
    ]);

    $response->assertCreated();
});

test('registered user can create a hosted game after their previous hosted game completed', function (): void {
    $user = User::factory()->create();
    $mode = QuizMode::factory()->create();
    $rule = ScoringRule::factory()->create();

    GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $mode->id,
        'scoring_rule_id' => $rule->id,
        'status' => SessionStatus::Completed,
    ]);

    $response = $this->actingAs($user, 'web')->postJson('/api/my/game-sessions', [
        'quiz_mode_id' => $mode->id,
        'scoring_rule_id' => $rule->id,
        'playlist_id' => null,
        'max_players' => 8,
        'is_public' => true,
    ]);

    $response->assertCreated();
});
