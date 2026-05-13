<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\GameSessionLobbySummaryData;
use App\Data\Models\GameSessionsLobbyResponseData;
use App\Enums\SessionStatus;
use App\Models\GameSession;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ListPublicLobbyGameSessions
{
    private const int MAX_ROWS = 50;

    public function __invoke(): Response
    {
        /** @var Collection<int, GameSession> $sessions */
        $sessions = GameSession::query()
            ->where('is_public', true)
            ->where('status', SessionStatus::Lobby)
            ->whereNull('started_at')
            ->with([
                'host:id,name',
                'quizMode:id,name',
                'playlist:id,name',
            ])
            ->withCount('participants')
            ->orderByDesc('created_at')
            ->limit(self::MAX_ROWS)
            ->get();

        $summaries = $sessions->map(static function (GameSession $session): GameSessionLobbySummaryData {
            $host = $session->host;
            $hostLabel = $host->name;
            if ($hostLabel === null || $hostLabel === '') {
                $hostLabel = 'Host';
            }

            return GameSessionLobbySummaryData::from([
                'id' => $session->id,
                'room_code' => $session->room_code,
                'host_display_name' => $hostLabel,
                'quiz_mode_name' => $session->quizMode->name,
                'playlist_name' => $session->playlist?->name,
                'max_players' => $session->max_players,
                'participant_count' => (int) $session->participants_count,
            ]);
        });

        return response()->json(GameSessionsLobbyResponseData::from([
            'sessions' => $summaries,
        ]));
    }
}
