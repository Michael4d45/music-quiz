<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Events\GameSessionUpdated;
use App\Features\Auth\Responses\MessageResponse;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use Symfony\Component\HttpFoundation\Response;

class LeaveGameSession
{
    public function __invoke(GameSession $gameSession): Response
    {
        $user = assertedUser();

        $participant = SessionParticipant::query()
            ->where('session_id', $gameSession->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant instanceof SessionParticipant) {
            return response()->json(MessageResponse::from([
                'message' => 'Not a participant in this session',
            ]), 404);
        }

        $participant->delete();

        event(new GameSessionUpdated($gameSession));

        return response()->json(MessageResponse::from([
            'message' => 'Left the session',
        ]));
    }
}
