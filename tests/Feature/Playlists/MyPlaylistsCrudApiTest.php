<?php

declare(strict_types=1);

use App\Enums\Visibility;
use App\Models\Playlist;
use App\Models\User;

test('owner can update playlist', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create([
        'name' => 'Old name',
        'visibility' => Visibility::Private,
    ]);

    $this
        ->actingAs($user, 'web')
        ->patchJson("/api/my/playlists/{$playlist->id}", [
            'name' => 'New name',
            'description' => 'Updated description',
            'visibility' => Visibility::Public->value,
        ])
        ->assertOk()
        ->assertJsonPath('name', 'New name')
        ->assertJsonPath('description', 'Updated description');

    $this->assertDatabaseHas('playlists', [
        'id' => $playlist->id,
        'name' => 'New name',
    ]);
});

test('owner can delete playlist', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create();

    $this
        ->actingAs($user, 'web')
        ->deleteJson("/api/my/playlists/{$playlist->id}")
        ->assertOk();

    $this->assertDatabaseMissing('playlists', ['id' => $playlist->id]);
});
