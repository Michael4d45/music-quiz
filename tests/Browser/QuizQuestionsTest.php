<?php

declare(strict_types=1);

use App\Models\User;

it('can view quiz questions page', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $logPath = setup_log_capture('quiz-questions.log');

    $page = visit_with_error_init('/quiz-questions')
        ->assertNoJavaScriptErrors()
        ->waitForText('My Quiz Questions', 5)
        ->assertSee('My Quiz Questions')
        ->assertSee('Create Question')
        ->screenshot(filename: 'quiz-questions-empty.png');

    assert_no_log_errors($logPath);
});

it('shows empty state when no questions exist', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    visit('/quiz-questions')
        ->assertNoJavaScriptErrors()
        ->waitForText('My Quiz Questions', 5)
        ->assertSee('My Quiz Questions')
        ->assertSee("You don't have any quiz questions yet.")
        ->assertSee('Create your first question');
});