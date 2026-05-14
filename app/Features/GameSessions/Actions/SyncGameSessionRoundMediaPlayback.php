<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Events\GameSessionRoundMediaPlayback;
use App\Features\GameSessions\Requests\SyncGameSessionRoundMediaPlaybackRequest;
use App\Models\GameSession;
use App\Models\SessionRound;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SyncGameSessionRoundMediaPlayback
{
    public function __invoke(
        SyncGameSessionRoundMediaPlaybackRequest $request,
        GameSession $gameSession,
        SessionRound $sessionRound,
    ): Response {
        Gate::authorize('update', $gameSession);

        if ((string) $sessionRound->session_id !== (string) $gameSession->id) {
            abort(404);
        }

        $playing = (bool) $request->validated('playing');
        $currentTimeSeconds = (float) $request->validated('current_time_seconds');

        $serverSeq = hrtime(true);

        broadcast(
            new GameSessionRoundMediaPlayback(
                $gameSession,
                $sessionRound->id,
                $playing,
                $currentTimeSeconds,
                $serverSeq,
            ),
        );

        return response()->noContent();
    }
}
