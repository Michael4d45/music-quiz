<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Data\Models\PlaylistItemData;
use App\Features\Playlists\Requests\StorePlaylistItemRequest;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AddPlaylistItem
{
    public function __invoke(
        StorePlaylistItemRequest $request,
        Playlist $playlist,
    ): Response {
        Gate::authorize('update', $playlist);

        /** @var array{question_id: string} $validated */
        $validated = $request->validated();
        $question = QuizQuestion::query()->findOrFail($validated['question_id']);
        Gate::authorize('view', $question);

        $maxSort = PlaylistItem::query()
            ->where('playlist_id', $playlist->id)
            ->max('sort_order');
        $nextOrder = is_numeric($maxSort) ? (int) $maxSort : 0;

        $item = PlaylistItem::query()->create([
            'playlist_id' => $playlist->id,
            'question_id' => $question->id,
            'sort_order' => $nextOrder + 1,
        ]);

        $item->load('question');

        return response()->json(PlaylistItemData::from($item), 201);
    }
}
