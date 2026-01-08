<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Response\SessionLobbyResponse;
use App\Enums\SessionStatus;
use App\Http\Requests\AuthRequest;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;

class StartGame
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        string $roomCode,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $session = GameSession::where('room_code', $roomCode)->firstOrFail();

        if ($session->host_id !== $user->id) {
            abort(403, 'Only the host can start the game');
        }

        $session->update([
            'status' => SessionStatus::InProgress,
            'started_at' => now(),
        ]);

        $session->load([
            'host',
            'quizMode',
            'scoringRule',
            'playlist',
            'participants.user',
        ]);

        return response()->json(SessionLobbyResponse::from([
            'session' => GameSessionData::from($session),
        ]));
    }
}
