<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Requests\JoinSessionRequest;
use App\Data\Response\SessionLobbyResponse;
use App\Enums\Role;
use App\Events\SessionEventOccurred;
use App\Http\Requests\AuthRequest;
use App\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class JoinSession
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        JoinSessionRequest $data,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $session = $data->gameSession();

        // Check if session is full
        $currentParticipantsCount = $session->participants()->count();
        if ($currentParticipantsCount >= $session->max_players) {
            throw ValidationException::withMessages([
                'room_code' => 'This session is full.',
            ]);
        }

        // Use user's name if guest_name is blank
        $guestName = $data->guest_name ?: $user->name;

        if (!$guestName) {
            throw ValidationException::withMessages([
                'guest_name' => 'You must provide a guest name.',
            ]);
        }

        // Check if guest name is already in use
        $existingParticipant = SessionParticipant::where(
            'session_id',
            $session->id,
        )
            ->where('guest_name', $guestName)
            ->first();

        if (
            $existingParticipant !== null
            && $existingParticipant->user_id !== $user->id
        ) {
            throw ValidationException::withMessages([
                'guest_name' => 'This guest name is already in use by another user.',
            ]);
        }

        if ($existingParticipant === null) {
            SessionParticipant::updateOrCreate([
                'user_id' => $user->id,
                'session_id' => $session->id,
            ], [
                'guest_name' => $guestName,
                'role' => Role::Player,
                'current_total_score' => 0,
                'is_connected' => true,
                'joined_at' => now(),
            ]);
        }

        $session->load([
            'host',
            'quizMode',
            'scoringRule',
            'playlist',
            'participants.user',
        ]);

        broadcast(new SessionEventOccurred($session, 'PlayerJoined', [
            'name' => $guestName,
        ]))->toOthers();

        return response()->json(SessionLobbyResponse::from([
            'session' => GameSessionData::from($session),
        ]));
    }
}
