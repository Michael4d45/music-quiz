<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\GameSessionData;
use App\Enums\SessionStatus;
use App\Features\GameSessions\Requests\CreateGameSessionRequest;
use App\Models\GameSession;
use App\Models\Playlist;
use App\Support\RoomCodeGenerator;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CreateGameSession
{
    public function __invoke(CreateGameSessionRequest $request): Response
    {
        $user = assertedUser();
        Gate::authorize('create', GameSession::class);

        if ($request->playlist_id !== null) {
            Gate::authorize(
                'view',
                Playlist::query()->findOrFail($request->playlist_id),
            );
        }

        $session = GameSession::query()->create([
            'host_id' => $user->id,
            'room_code' => RoomCodeGenerator::generate(),
            'status' => SessionStatus::Lobby,
            'quiz_mode_id' => $request->quiz_mode_id,
            'scoring_rule_id' => $request->scoring_rule_id,
            'playlist_id' => $request->playlist_id,
            'max_players' => $request->max_players,
            'is_public' => $request->is_public,
            'started_at' => null,
            'ended_at' => null,
        ]);

        $session->load([
            'host:id,name,is_guest,is_admin',
            'quizMode:id,name,description,allows_host_override,requires_manual_scoring,created_at,updated_at',
            'scoringRule:id,name,base_points,decay_factor,max_time_ms,streak_bonus_enabled,streak_multiplier,created_at,updated_at',
            'playlist',
        ]);

        return response()->json(GameSessionData::from($session), 201);
    }
}
