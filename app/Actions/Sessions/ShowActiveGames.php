<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Response\ActiveGamesResponse;
use App\Enums\SessionStatus;
use App\Http\Requests\AuthRequest;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;

class ShowActiveGames
{
    /**
     * Display the user's active game sessions.
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        $user = $request->assertedUser();

        $sessions = GameSession::where('host_id', $user->id)
            ->whereIn('status', [
                SessionStatus::Lobby,
                SessionStatus::InProgress,
            ])
            ->with(['host', 'quizMode', 'playlist', 'participants'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(ActiveGamesResponse::from([
            'sessions' => GameSessionData::collect($sessions),
        ]));
    }
}
