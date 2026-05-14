<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Features\Playlists\Requests\UpdatePlaylistItemPositionRequest;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class UpdatePlaylistItemPosition
{
    public function __invoke(
        Request $request,
        Playlist $playlist,
        PlaylistItem $playlistItem,
    ): Response {
        Gate::authorize('update', $playlist);

        if ($playlistItem->playlist_id !== $playlist->id) {
            abort(404);
        }

        $validatedResult = UpdatePlaylistItemPositionRequest::validate($request->only([
            'before_item_id',
        ]));
        $validated = is_array($validatedResult)
            ? $validatedResult
            : $validatedResult->toArray();

        /** @var string|null $beforeItemId */
        $beforeItemId = $validated['before_item_id'] ?? null;

        if ($beforeItemId === $playlistItem->id) {
            throw ValidationException::withMessages([
                'before_item_id' => 'Cannot place an item before itself.',
            ]);
        }

        $all = PlaylistItem::query()
            ->where('playlist_id', $playlist->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $without = $all->filter(
            static fn(PlaylistItem $item): bool => (
                $item->id !== $playlistItem->id
            ),
        )->values();

        if ($beforeItemId !== null) {
            $exists = $without->contains(
                static fn(PlaylistItem $item): bool => (
                    $item->id === $beforeItemId
                ),
            );
            if (!$exists) {
                throw ValidationException::withMessages([
                    'before_item_id' => 'Must reference another item in this playlist.',
                ]);
            }
        }

        $insertIndex = $beforeItemId === null
            ? $without->count()
            : (int) $without->search(
                static fn(PlaylistItem $item): bool => (
                    $item->id === $beforeItemId
                ),
            );

        /** @var list<PlaylistItem> $orderedModels */
        $orderedModels = $without->all();
        array_splice($orderedModels, $insertIndex, 0, [$playlistItem]);
        $ordered = collect($orderedModels);

        DB::transaction(function () use ($ordered, $playlistItem): void {
            $newIndex = $ordered->search(
                static fn(PlaylistItem $item): bool => (
                    $item->id === $playlistItem->id
                ),
            );
            if ($newIndex === false) {
                return;
            }

            $left = $newIndex > 0
                ? (int) $ordered->get($newIndex - 1)->sort_order
                : null;
            $right = $newIndex < ($ordered->count() - 1)
                ? (int) $ordered->get($newIndex + 1)->sort_order
                : null;

            $newSort = $this->midpointSortOrder($left, $right);

            if ($newSort !== null) {
                $hasConflict = PlaylistItem::query()
                    ->where('playlist_id', $playlistItem->playlist_id)
                    ->whereKeyNot($playlistItem->id)
                    ->where('sort_order', $newSort)
                    ->exists();
                if (!$hasConflict) {
                    PlaylistItem::query()
                        ->whereKey($playlistItem->id)
                        ->update(['sort_order' => $newSort]);

                    return;
                }
            }

            $this->reassignSortOrders($ordered);
        });

        return app(ListPlaylistItems::class)($playlist);
    }

    private function midpointSortOrder(
        null|int $left,
        null|int $right,
    ): null|int {
        if ($left === null && $right === null) {
            return 100;
        }

        if ($left === null) {
            if ($right === null || $right <= 1) {
                return null;
            }

            $candidate = $right - 100;

            if ($candidate >= 1 && $candidate < $right) {
                return $candidate;
            }

            $half = (int) floor($right / 2);

            return $half >= 1 && $half < $right ? $half : null;
        }

        if ($right === null) {
            if ($left >= (PHP_INT_MAX - 1)) {
                return null;
            }

            $candidate = $left + 100;

            return $candidate > $left ? $candidate : null;
        }

        if ($left >= ($right - 1)) {
            return null;
        }

        if (($left + 100) < $right) {
            return $left + 100;
        }

        $mid = (int) floor(($left + $right) / 2);

        return $mid > $left && $mid < $right ? $mid : null;
    }

    /**
     * @param  Collection<int, PlaylistItem>  $ordered
     */
    private function reassignSortOrders(Collection $ordered): void
    {
        $next = 100;
        foreach ($ordered as $item) {
            if ((int) $item->sort_order !== $next) {
                PlaylistItem::query()
                    ->whereKey($item->id)
                    ->update(['sort_order' => $next]);
            }
            $next += 100;
        }
    }
}
