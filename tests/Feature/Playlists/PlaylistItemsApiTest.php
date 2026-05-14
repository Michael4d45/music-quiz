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
            'sort_order' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->getJson("/api/my/playlists/{$playlist->id}/items")
            ->assertOk()
            ->assertJsonPath('playlist.name', 'Evening set')
            ->assertJsonCount(1, 'items');
    }

    public function test_add_first_playlist_item_sort_order_is_hundred(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $question = QuizQuestion::factory()->for($user)->create();

        $this->actingAs($user, 'web')
            ->postJson("/api/my/playlists/{$playlist->id}/items", [
                'question_id' => $question->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('playlist_items', [
            'question_id' => $question->id,
            'sort_order' => 100,
        ]);
    }

    public function test_add_playlist_item_appends_sort_order_by_hundreds(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $q1 = QuizQuestion::factory()->for($user)->create();
        $q2 = QuizQuestion::factory()->for($user)->create();
        PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q1->id,
            'sort_order' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->postJson("/api/my/playlists/{$playlist->id}/items", [
                'question_id' => $q2->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('playlist_items', [
            'question_id' => $q2->id,
            'sort_order' => 200,
        ]);
    }

    public function test_owner_can_move_playlist_item_up_with_single_patch(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $q1 = QuizQuestion::factory()->for($user)->create();
        $q2 = QuizQuestion::factory()->for($user)->create();
        $itemA = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q1->id,
            'sort_order' => 100,
        ]);
        $itemB = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q2->id,
            'sort_order' => 200,
        ]);

        $this->actingAs($user, 'web')
            ->patchJson(
                "/api/my/playlists/{$playlist->id}/items/{$itemB->id}",
                ['before_item_id' => $itemA->id],
            )
            ->assertOk()
            ->assertJsonPath('playlist.id', $playlist->id);

        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemB->id,
            'sort_order' => 50,
        ]);
        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemA->id,
            'sort_order' => 100,
        ]);
    }

    public function test_move_rejects_unknown_before_item_id(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $question = QuizQuestion::factory()->for($user)->create();
        $item = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->patchJson(
                "/api/my/playlists/{$playlist->id}/items/{$item->id}",
                ['before_item_id' => '00000000-0000-4000-8000-000000000001'],
            )
            ->assertStatus(422);
    }

    public function test_move_rebalances_when_no_integer_fits_between_neighbors(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $q1 = QuizQuestion::factory()->for($user)->create();
        $q2 = QuizQuestion::factory()->for($user)->create();
        $q3 = QuizQuestion::factory()->for($user)->create();
        $itemA = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q1->id,
            'sort_order' => 100,
        ]);
        $itemB = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q2->id,
            'sort_order' => 101,
        ]);
        $itemC = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $q3->id,
            'sort_order' => 102,
        ]);

        $this->actingAs($user, 'web')
            ->patchJson(
                "/api/my/playlists/{$playlist->id}/items/{$itemC->id}",
                ['before_item_id' => $itemB->id],
            )
            ->assertOk();

        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemA->id,
            'sort_order' => 100,
        ]);
        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemC->id,
            'sort_order' => 200,
        ]);
        $this->assertDatabaseHas('playlist_items', [
            'id' => $itemB->id,
            'sort_order' => 300,
        ]);
    }

    public function test_move_rejects_before_self(): void
    {
        $user = User::factory()->create();
        $playlist = Playlist::factory()->for($user)->create();
        $question = QuizQuestion::factory()->for($user)->create();
        $item = PlaylistItem::factory()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->patchJson(
                "/api/my/playlists/{$playlist->id}/items/{$item->id}",
                ['before_item_id' => $item->id],
            )
            ->assertStatus(422);
    }
}
