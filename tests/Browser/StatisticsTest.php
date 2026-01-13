<?php

declare(strict_types=1);

use App\Models\User;

it('can view statistics page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $logPath = setup_log_capture('statistics.log');

    $page = visit_with_error_init('/statistics')
        ->assertNoJavaScriptErrors()
        ->waitForText('My Statistics', 5)
        ->assertSee('My Statistics')
        ->assertSee('Recent Games')
        ->screenshot(filename: 'statistics.png');

    assert_no_log_errors($logPath);
});
