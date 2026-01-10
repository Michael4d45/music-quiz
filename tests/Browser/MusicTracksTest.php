<?php

declare(strict_types=1);

use App\Models\User;

it('can view music tracks page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $logPath = setup_log_capture('music-tracks.log');

    $page = visit_with_error_init('/music-tracks')
        ->assertNoJavaScriptErrors()
        ->waitForText('My Music Tracks', 5)
        ->assertSee('My Music Tracks')
        ->assertSee('Add Track')
        ->screenshot(filename: 'music-tracks-empty.png');

    assert_no_log_errors($logPath);
});

it('shows empty state when no tracks exist', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit('/music-tracks')
        ->assertNoJavaScriptErrors()
        ->waitForText('My Music Tracks', 5)
        ->assertSee('My Music Tracks')
        ->assertSee("You don't have any music tracks yet.")
        ->assertSee('Add your first track');
});