<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Requests\CreateSessionRequest;
use App\Data\Response\SessionLobbyResponse;
use App\Enums\Role;
use App\Enums\SessionStatus;
use App\Http\Requests\AuthRequest;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CreateSession
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        CreateSessionRequest $data,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        // Generate unique room code (3 letters + 3 numbers)
        do {
            $roomCode = Str::upper(Str::random(3)) . fake()->numerify('###');
        } while (GameSession::where('room_code', $roomCode)->exists());

        $session = GameSession::create([
            'host_id' => $user->id,
            'room_code' => $roomCode,
            'status' => SessionStatus::Lobby,
            'quiz_mode_id' => $data->quiz_mode_id,
            'scoring_rule_id' => $data->scoring_rule_id,
            'playlist_id' => $data->playlist_id,
            'max_players' => $data->max_players,
        ]);

        // Create the host as a participant
        SessionParticipant::create([
            'session_id' => $session->id,
            'user_id' => $user->id,
            'role' => Role::Host,
            'current_total_score' => 0,
            'is_connected' => true,
            'joined_at' => now(),
        ]);

        $session->load([
            'host',
            'quizMode',
            'scoringRule',
            'playlist',
            'participants',
        ]);

        return response()->json(SessionLobbyResponse::from([
            'session' => GameSessionData::from($session),
        ]), 201);
    }
}
