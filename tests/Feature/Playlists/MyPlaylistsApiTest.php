<?php

declare(strict_types=1);

use App\Models\Playlist;
use App\Models\User;

test('registered user can list and create playlists', function (): void {
    $user = User::factory()->create();

    $list = $this->actingAs($user, 'web')->getJson('/api/my/playlists');
    $list->assertSuccessful();
    $list->assertJsonCount(0, 'playlists');

    $create = $this->actingAs($user, 'web')->postJson('/api/my/playlists', [
        'name' => 'My list',
        'description' => 'Test',
    ]);
    $create->assertCreated();
    $create->assertJsonPath('name', 'My list');

    expect(Playlist::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('guest cannot access my playlists', function (): void {
    $guest = User::factory()->guest()->create();

    $this->actingAs($guest, 'web')->getJson('/api/my/playlists')->assertForbidden();
});
