<?php

declare(strict_types=1);

use App\Models\User;

it('can view active games page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $logPath = setup_log_capture('active-games.log');

    $page = visit_with_error_init('/active-games')
        ->assertNoJavaScriptErrors()
        ->waitForText('Active Games', 5)
        ->assertSee('Active Games')
        ->assertSee('Create Game')
        ->screenshot(filename: 'active-games.png');

    assert_no_log_errors($logPath);
});

it('can view create session page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit('/sessions/create')
        ->assertNoJavaScriptErrors()
        ->waitForText('Create New Game', 5)
        ->assertSee('Create New Game')
        ->assertSee('Max Players')
        ->assertSee('Create Game');
});