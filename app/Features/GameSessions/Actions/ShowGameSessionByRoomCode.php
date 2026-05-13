<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\GameSessionData;
use App\Enums\SessionStatus;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ShowGameSessionByRoomCode
{
    public function __invoke(string $roomCode): Response
    {
        $user = assertedUser();
        $normalized = strtoupper($roomCode);

        /** @var GameSession|null $session */
        $session = GameSession::query()
            ->whereRaw('upper(room_code) = ?', [$normalized])
            ->with([
                'host:id,name,is_guest,is_admin',
                'quizMode:id,name,description,allows_host_override,requires_manual_scoring,created_at,updated_at',
                'scoringRule:id,name,base_points,decay_factor,max_time_ms,streak_bonus_enabled,streak_multiplier,created_at,updated_at',
                'playlist',
                'participants' => static function ($query): void {
                    $query->orderBy('joined_at');
                },
                'participants.user:id,name,is_guest,is_admin',
            ])
            ->first();

        if (!$session instanceof GameSession) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        if (!$this->userMayViewSession($user->id, $session)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(GameSessionData::from($session));
    }

    private function userMayViewSession(string $userId, GameSession $session): bool
    {
        if ($session->host_id === $userId) {
            return true;
        }

        if (
            $session->participants->contains(static fn($p) => (string) $p->user_id === $userId)
        ) {
            return true;
        }

        return $session->status === SessionStatus::Lobby;
    }
}
