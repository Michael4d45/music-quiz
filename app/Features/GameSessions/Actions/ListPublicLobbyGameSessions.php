<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\GameSessionLobbyCurrentSessionData;
use App\Data\Models\GameSessionLobbySummaryData;
use App\Data\Models\GameSessionsLobbyResponseData;
use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

        $user = Auth::user();
        $current = $user instanceof User
            ? $this->resolveCurrentActiveSession($user)
            : null;

        if ($current !== null) {
            $summaries = $summaries->sortByDesc(static function (GameSessionLobbySummaryData $row) use (
                $current,
            ): int {
                return $row->id === $current->id ? 1 : 0;
            })->values();
        }

        return response()->json(GameSessionsLobbyResponseData::from([
            'sessions' => $summaries,
            'current_session' => $current,
        ]));
    }

    private function resolveCurrentActiveSession(User $user): null|GameSessionLobbyCurrentSessionData
    {
        $participant = SessionParticipant::query()
            ->where('user_id', $user->id)
            ->whereHas('session', static function ($query): void {
                $query->where('status', '!=', SessionStatus::Completed);
            })
            ->orderByDesc('joined_at')
            ->first();

        if ($participant !== null) {
            $session = GameSession::query()
                ->whereKey($participant->session_id)
                ->with([
                    'host:id,name',
                    'quizMode:id,name',
                    'playlist:id,name',
                ])
                ->withCount('participants')
                ->first();

            if ($session instanceof GameSession) {
                return GameSessionLobbyCurrentSessionData::fromGameSession(
                    $session,
                );
            }
        }

        $hosted = GameSession::query()
            ->where('host_id', $user->id)
            ->where('status', '!=', SessionStatus::Completed)
            ->with([
                'host:id,name',
                'quizMode:id,name',
                'playlist:id,name',
            ])
            ->withCount('participants')
            ->orderByDesc('created_at')
            ->first();

        if ($hosted instanceof GameSession) {
            return GameSessionLobbyCurrentSessionData::fromGameSession($hosted);
        }

        return null;
    }
}
