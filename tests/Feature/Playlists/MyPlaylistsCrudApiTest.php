<?php

declare(strict_types=1);

namespace Tests\Feature\Playlists;

use App\Enums\PlaylistStatus;
use App\Enums\Visibility;
use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPlaylistsCrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_playlist(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()
            ->for($user)
            ->create([
                'name' => 'Old name',
                'status' => PlaylistStatus::Draft,
                'visibility' => Visibility::Private,
            ]);

        $this->actingAs($user, 'web')
            ->patchJson("/api/my/playlists/{$playlist->id}", [
                'name' => 'New name',
                'description' => 'Updated description',
                'status' => PlaylistStatus::Published->value,
                'visibility' => Visibility::Public->value,
            ])
            ->assertOk()
            ->assertJsonPath('name', 'New name')
            ->assertJsonPath('description', 'Updated description');

        $this->assertDatabaseHas('playlists', [
            'id' => $playlist->id,
            'name' => 'New name',
        ]);
    }

    public function test_owner_can_delete_playlist(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();

        $this->actingAs($user, 'web')
            ->deleteJson("/api/my/playlists/{$playlist->id}")
            ->assertOk();

        $this->assertDatabaseMissing('playlists', ['id' => $playlist->id]);
    }
}
