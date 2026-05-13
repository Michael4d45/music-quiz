<?php

declare(strict_types=1);

test('guest session cannot call registered-only auth endpoints', function (): void {
    $this->getJson('/api/user')->assertSuccessful()->assertJsonPath('is_guest', true);

    $this->postJson('/api/disconnect-google')->assertForbidden();
});
