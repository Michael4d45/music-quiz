<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Requests\JoinSessionRequest;
use App\Data\Response\SessionLobbyResponse;
use App\Enums\Role;
use App\Events\SessionEventOccurred;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JoinSession
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        JoinSessionRequest $data,
        Request $request,
    ): JsonResponse {
        $user = $request->user();
        $token = null;

        if ($user === null) {
            $guestName = $data->guest_name ?: 'Guest ' . Str::random(5);
            $user = User::create([
                'name' => $guestName,
                'is_guest' => true,
            ]);
            $token = $user->createToken('guest-token')->plainTextToken;
        }

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

        if ($user->is_guest && !$guestName) {
            throw ValidationException::withMessages([
                'guest_name' => 'You must provide a guest name.',
            ]);
        }

        if ($guestName) {
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
        } else {
            SessionParticipant::updateOrCreate([
                'user_id' => $user->id,
                'session_id' => $session->id,
            ], [
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
            'name' => $guestName ?: $user->name,
        ]))->toOthers();

        return response()->json(SessionLobbyResponse::from([
            'session' => GameSessionData::from($session),
            'token' => $token,
        ]));
    }
}
