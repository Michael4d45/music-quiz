<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Response\SessionLobbyResponse;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;

class ShowSessionLobby
{
    /**
     * Display the session lobby data.
     */
    public function __invoke(string $roomCode): JsonResponse
    {
        $session = GameSession::where('room_code', $roomCode)
            ->with([
                'host',
                'quizMode',
                'scoringRule',
                'playlist',
                'participants.user',
            ])
            ->firstOrFail();

        return response()->json(SessionLobbyResponse::from([
            'session' => GameSessionData::from($session),
        ]));
    }
}
