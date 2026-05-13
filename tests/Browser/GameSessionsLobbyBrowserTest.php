<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    setup_log_capture('game-sessions-lobby.log');
});

afterEach(function (): void {
    assert_no_log_errors(storage_path('logs/game-sessions-lobby.log'));
});

it('game lobby page loads without JS errors when authenticated', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit_with_custom_init('/game-sessions/lobby')
        ->assertNoJavaScriptErrors()
        ->waitForText('Game lobby', 10)
        ->assertSee('Game lobby');
});

it('displays back to home and refresh on lobby page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit_with_custom_init('/game-sessions/lobby')
        ->assertNoJavaScriptErrors()
        ->waitForText('Game lobby', 10)
        ->assertSee('Refresh list');
});

it('game lobby page is usable on mobile width', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit_with_custom_init('/game-sessions/lobby')
        ->assertNoJavaScriptErrors()
        ->waitForText('Game lobby', 10)
        ->resize(375, 667)
        ->assertSee('Game lobby');
});
