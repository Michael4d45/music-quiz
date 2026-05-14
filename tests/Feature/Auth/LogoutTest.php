<?php

declare(strict_types=1);

use App\Models\User;

it('can logout when authenticated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson('/logout');

    assert_status($response, 200)->assertJson([
        'message' => 'Logged out successfully',
    ]);
});
