<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;

test('user edit form does not expose the stored password hash', function (): void {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('filament.admin.resources.users.edit', $admin));

    $response->assertOk();
    $response->assertDontSee($admin->password, false);
});