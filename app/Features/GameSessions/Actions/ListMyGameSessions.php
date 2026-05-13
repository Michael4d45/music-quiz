<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\GameSessionData;
use App\Data\Responses\MyGameSessionsResponseData;
use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\Playlist;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ListMyGameSessions
{
    public function __invoke(): Response
    {
        $user = assertedUser();
        Gate::authorize('viewAny', GameSession::class);

        $sessions = GameSession::query()
            ->where('host_id', $user->id)
            ->with([
                'host:id,name,is_guest,is_admin',
                'quizMode:id,name,description,allows_host_override,requires_manual_scoring,created_at,updated_at',
                'scoringRule:id,name,base_points,decay_factor,max_time_ms,streak_bonus_enabled,streak_multiplier,created_at,updated_at',
                'playlist',
            ])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $payload = $sessions
            ->map(static fn(GameSession $session): GameSessionData => GameSessionData::from($session))
            ->all();

        return response()->json(MyGameSessionsResponseData::from([
            'sessions' => $payload,
        ]));
    }
}
