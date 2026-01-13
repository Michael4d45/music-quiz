<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Events\SessionEventOccurred;
use App\Http\Requests\AuthRequest;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;

class LeaveSession
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

        SessionParticipant::where('session_id', $session->id)
            ->where('user_id', $user->id)
            ->update(['is_connected' => false]);

        broadcast(new SessionEventOccurred($session, 'PlayerLeft', [
            'user_id' => $user->id,
            'name' => $user->name,
        ]))->toOthers();

        return response()->json(['message' => 'Left session successfully']);
    }
}
