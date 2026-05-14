<?php

declare(strict_types=1);

use App\Models\MusicTrack;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use App\Models\User;

test('list playlist items includes playlist summary', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create([
        'name' => 'Evening set',
    ]);
    $question = QuizQuestion::factory()->for($user)->create();
    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $this
        ->actingAs($user, 'web')
        ->getJson("/api/my/playlists/{$playlist->id}/items")
        ->assertOk()
        ->assertJsonPath('playlist.name', 'Evening set')
        ->assertJsonCount(1, 'items');
});

test('list playlist items includes nested question track', function (): void {
    $user = User::factory()->create();
    $track = MusicTrack::factory()->create([
        'user_id' => $user->id,
    ]);
    $question = QuizQuestion::factory()->for($user)->create([
        'track_id' => $track->id,
    ]);
    $playlist = Playlist::factory()->for($user)->create([
        'name' => 'With track',
    ]);
    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $this
        ->actingAs($user, 'web')
        ->getJson("/api/my/playlists/{$playlist->id}/items")
        ->assertOk()
        ->assertJsonPath('items.0.question.track.id', $track->id);
});

test('add first playlist item sort order is hundred', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create();
    $question = QuizQuestion::factory()->for($user)->create();

    $this
        ->actingAs($user, 'web')
        ->postJson("/api/my/playlists/{$playlist->id}/items", [
            'question_id' => $question->id,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('playlist_items', [
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);
});

test('add playlist item appends sort order by hundreds', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create();
    $q1 = QuizQuestion::factory()->for($user)->create();
    $q2 = QuizQuestion::factory()->for($user)->create();
    PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $q1->id,
        'sort_order' => 100,
    ]);

    $this
        ->actingAs($user, 'web')
        ->postJson("/api/my/playlists/{$playlist->id}/items", [
            'question_id' => $q2->id,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('playlist_items', [
        'question_id' => $q2->id,
        'sort_order' => 200,
    ]);
});

test('owner can move playlist item up with single patch', function (): void {
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

    $this
        ->actingAs($user, 'web')
        ->patchJson("/api/my/playlists/{$playlist->id}/items/{$itemB->id}", [
            'before_item_id' => $itemA->id,
        ])
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
});

test('move rejects unknown before item id', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create();
    $question = QuizQuestion::factory()->for($user)->create();
    $item = PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $this
        ->actingAs($user, 'web')
        ->patchJson("/api/my/playlists/{$playlist->id}/items/{$item->id}", [
            'before_item_id' => '00000000-0000-4000-8000-000000000001',
        ])
        ->assertUnprocessable();
});

test('move rebalances when no integer fits between neighbors', function (): void {
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

    $this
        ->actingAs($user, 'web')
        ->patchJson("/api/my/playlists/{$playlist->id}/items/{$itemC->id}", [
            'before_item_id' => $itemB->id,
        ])
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
});

test('move rejects before self', function (): void {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create();
    $question = QuizQuestion::factory()->for($user)->create();
    $item = PlaylistItem::factory()->create([
        'playlist_id' => $playlist->id,
        'question_id' => $question->id,
        'sort_order' => 100,
    ]);

    $this
        ->actingAs($user, 'web')
        ->patchJson("/api/my/playlists/{$playlist->id}/items/{$item->id}", [
            'before_item_id' => $item->id,
        ])
        ->assertUnprocessable();
});
