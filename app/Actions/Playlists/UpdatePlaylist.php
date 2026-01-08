<?php

declare(strict_types=1);

namespace App\Actions\Playlists;

use App\Data\Models\PlaylistData;
use App\Data\Requests\UpdatePlaylistRequest;
use App\Data\Response\PlaylistResponse;
use App\Http\Requests\AuthRequest;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdatePlaylist
{
    public function __invoke(
        UpdatePlaylistRequest $data,
        Playlist $playlist,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        if ($playlist->user_id !== $user->id) {
            abort(403, 'You can only edit your own playlists');
        }

        DB::transaction(function () use ($data, $playlist) {
            $playlist->update([
                'name' => $data->name,
                'description' => $data->description,
                'is_public' => $data->is_public,
            ]);

            // Remove all existing items
            $playlist->items()->delete();

            // Add new questions to playlist
            foreach ($data->question_ids as $index => $questionId) {
                PlaylistItem::create([
                    'playlist_id' => $playlist->id,
                    'question_id' => $questionId,
                    'sort_order' => $index + 1,
                    'added_at' => now(),
                ]);
            }
        });

        $playlist->load(['user', 'items.question']);

        return response()->json(PlaylistResponse::from([
            'playlist' => PlaylistData::from($playlist),
        ]));
    }
}
