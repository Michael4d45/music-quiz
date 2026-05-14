<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Features\Playlists\Requests\ReorderPlaylistItemsRequest;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ReorderPlaylistItems
{
    public function __invoke(
        ReorderPlaylistItemsRequest $request,
        Playlist $playlist,
    ): Response {
        Gate::authorize('update', $playlist);

        /** @var list<string> $ids */
        $ids = $request->validated('item_ids');

        $existingIds = PlaylistItem::query()
            ->where('playlist_id', $playlist->id)
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $sortedIncoming = collect($ids)->sort()->values()->all();

        if ($existingIds !== $sortedIncoming) {
            throw ValidationException::withMessages([
                'item_ids' => 'Must contain each playlist item exactly once.',
            ]);
        }

        DB::transaction(function () use ($ids, $playlist): void {
            foreach ($ids as $index => $itemId) {
                PlaylistItem::query()
                    ->where('playlist_id', $playlist->id)
                    ->where('id', $itemId)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return app(ListPlaylistItems::class)($playlist);
    }
}
