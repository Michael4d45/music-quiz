<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Enums\SessionStatus;
use App\Features\Auth\Responses\MessageResponse;
use App\Models\GameSession;
use App\Support\GameSessions\GameSessionRoomViewBuilder;
use App\Support\GameSessions\ValidGameRoomCode;
use Symfony\Component\HttpFoundation\Response;

class ShowGameSessionByRoomCode
{
    public function __invoke(string $roomCode): Response
    {
        $user = assertedUser();

        if (!ValidGameRoomCode::isValid($roomCode)) {
            return response()->json(MessageResponse::from([
                'message' => ValidGameRoomCode::invalidFormatMessage(),
            ]), 422);
        }

        $normalized = ValidGameRoomCode::normalize($roomCode);

        /** @var GameSession|null $session */
        $session = GameSession::query()
            ->whereRaw('upper(room_code) = ?', [$normalized])
            ->with(StartGameSession::roomEagerLoads())
            ->first();

        if (!$session instanceof GameSession) {
            return response()->json(MessageResponse::from([
                'message' => 'Room not found',
            ]), 404);
        }

        if (!$this->userMayViewSession($user->id, $session)) {
            return response()->json(MessageResponse::from([
                'message' => 'Forbidden',
            ]), 403);
        }

        return response()->json(
            GameSessionRoomViewBuilder::build($session, $user),
        );
    }

    private function userMayViewSession(
        string $userId,
        GameSession $session,
    ): bool {
        if ($session->host_id === $userId) {
            return true;
        }

        if ($session->participants->contains(
            static fn($p) => (string) $p->user_id === $userId,
        )) {
            return true;
        }

        return $session->status === SessionStatus::Lobby;
    }
}
