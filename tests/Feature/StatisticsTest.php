<?php

declare(strict_types=1);

use App\Models\GameSession;
use App\Models\QuizMode;
use App\Models\ScoringRule;
use App\Models\User;
use App\Models\UserStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access statistics page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('statistics'));

    $response
        ->assertStatus(200)
        ->assertInertia(fn($page) => $page->component('statistics/index'));
});

it('displays user statistics when available', function (): void {
    $user = User::factory()->create();
    $statistic = UserStatistic::factory()->create([
        'user_id' => $user->id,
        'total_games_played' => 10,
        'total_points' => 500,
        'best_streak' => 5,
    ]);

    $response = $this->actingAs($user)->get(route('statistics'));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page
                ->component('statistics/index')
                ->where('statistic.total_games_played', 10)
                ->where('statistic.total_points', 500)
                ->where('statistic.best_streak', 5),
        );
});

it('displays recent sessions on statistics page', function (): void {
    $user = User::factory()->create();
    $quizMode = QuizMode::factory()->create();
    $scoringRule = ScoringRule::factory()->create();
    $session1 = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
    ]);
    $session2 = GameSession::factory()->create([
        'host_id' => $user->id,
        'quiz_mode_id' => $quizMode->id,
        'scoring_rule_id' => $scoringRule->id,
    ]);

    $response = $this->actingAs($user)->get(route('statistics'));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page->component('statistics/index')->has(
                'recent_sessions',
                2,
            ),
        );
});

it('can access leaderboard page', function (): void {
    $response = $this->get(route('leaderboard'));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page->component('statistics/leaderboard'),
        );
});

it('displays top players on leaderboard', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $stat1 = UserStatistic::factory()->create([
        'user_id' => $user1->id,
        'total_points' => 1000,
    ]);
    $stat2 = UserStatistic::factory()->create([
        'user_id' => $user2->id,
        'total_points' => 500,
    ]);

    $response = $this->get(route('leaderboard'));

    $response
        ->assertStatus(200)
        ->assertInertia(
            fn($page) => $page
                ->component('statistics/leaderboard')
                ->has('players', 2)
                ->where('players.0.total_points', 1000)
                ->where('players.1.total_points', 500),
        );
});
