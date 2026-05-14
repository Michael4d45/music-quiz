<?php

declare(strict_types=1);

namespace Tests\Feature\Playlists;

use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaylistItemsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_playlist_items_includes_playlist_summary(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create(['name' => 'Evening set']);
        $question = QuizQuestion::factory()->for($user)->create();
        PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => 1,
        ]);

        $this->actingAs($user, 'web')
            ->getJson("/api/my/playlists/{$playlist->id}/items")
            ->assertOk()
            ->assertJsonPath('playlist.name', 'Evening set')
            ->assertJsonCount(1, 'items');
    }

    public function test_owner_can_reorder_playlist_items(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $q1 = QuizQuestion::factory()->for($user)->create();
        $q2 = QuizQuestion::factory()->for($user)->create();
        $itemA = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q1->id,
            'sort_order' => 1,
        ]);
        $itemB = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q2->id,
            'sort_order' => 2,
        ]);

        $this->actingAs($user, 'web')
            ->patchJson("/api/my/playlists/{$playlist->id}/items/order", [
                'item_ids' => [$itemB->id, $itemA->id],
            ])
            ->assertOk()
            ->assertJsonPath('playlist.id', $playlist->id);

        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemB->id,
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemA->id,
            'sort_order' => 2,
        ]);
    }

    public function test_reorder_rejects_wrong_item_set(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $question = QuizQuestion::factory()->for($user)->create();
        $item = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => 1,
        ]);

        $this->actingAs($user, 'web')
            ->patchJson("/api/my/playlists/{$playlist->id}/items/order", [
                'item_ids' => [$item->id, $item->id],
            ])
            ->assertStatus(422);
    }
}
