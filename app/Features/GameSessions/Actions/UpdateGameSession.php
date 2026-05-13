<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\GameSessionData;
use App\Enums\SessionStatus;
use App\Features\GameSessions\Requests\UpdateGameSessionRequest;
use App\Models\GameSession;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UpdateGameSession
{
    public function __invoke(
        Request $request,
        GameSession $gameSession,
    ): Response {
        Gate::authorize('update', $gameSession);

        if ($gameSession->status !== SessionStatus::Lobby) {
            abort(422, 'Only sessions in the lobby can be updated.');
        }

        $validatedResult = UpdateGameSessionRequest::validate($request->only([
            'is_public',
            'max_players',
            'playlist_id',
        ]));
        $data = is_array($validatedResult)
            ? $validatedResult
            : $validatedResult->toArray();

        if (array_key_exists('playlist_id', $data)) {
            if ($data['playlist_id'] !== null) {
                Gate::authorize(
                    'view',
                    Playlist::query()->findOrFail($data['playlist_id']),
                );
            }
        }

        $gameSession->fill($data);
        $gameSession->save();

        $gameSession->load([
            'host:id,name,is_guest,is_admin',
            'quizMode:id,name,description,allows_host_override,requires_manual_scoring,created_at,updated_at',
            'scoringRule:id,name,base_points,decay_factor,max_time_ms,streak_bonus_enabled,streak_multiplier,created_at,updated_at',
            'playlist',
        ]);

        return response()->json(GameSessionData::from($gameSession));
    }
}
