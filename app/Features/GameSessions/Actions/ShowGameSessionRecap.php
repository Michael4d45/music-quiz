<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Models\GameSession;
use App\Support\GameSessions\GameSessionRoomViewBuilder;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ShowGameSessionRecap
{
    public function __invoke(GameSession $gameSession): Response
    {
        $user = assertedUser();
        Gate::authorize('view', $gameSession);

        $gameSession->load(StartGameSession::roomEagerLoads());

        return response()->json(
            GameSessionRoomViewBuilder::build($gameSession, $user),
        );
    }
}
