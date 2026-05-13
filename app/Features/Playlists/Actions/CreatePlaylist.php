<?php

declare(strict_types=1);

namespace App\Features\Playlists\Actions;

use App\Data\Models\PlaylistData;
use App\Enums\PlaylistStatus;
use App\Enums\QuestionOrder;
use App\Enums\Visibility;
use App\Features\Playlists\Requests\CreatePlaylistRequest;
use App\Models\Playlist;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CreatePlaylist
{
    public function __invoke(CreatePlaylistRequest $request): Response
    {
        Gate::authorize('create', Playlist::class);

        $user = assertedUser();

        $playlist = Playlist::query()->create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? PlaylistStatus::Draft,
            'visibility' => $request->visibility ?? Visibility::Private,
            'tags' => null,
            'estimated_duration_minutes' => null,
            'target_audience' => null,
            'question_order' => QuestionOrder::Fixed,
            'default_time_limit_seconds' => null,
            'scoring_rule_id' => null,
            'play_count' => 0,
        ]);

        return response()->json(PlaylistData::from($playlist), 201);
    }
}
