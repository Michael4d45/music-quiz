<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\GameSessionData;
use App\Data\Response\SessionLobbyResponse;
use App\Enums\SessionStatus;
use App\Http\Requests\AuthRequest;
use App\Jobs\EndRound;
use App\Models\GameSession;
use App\Models\SessionRound;
use Illuminate\Http\JsonResponse;

class StartGame
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

        if ($session->host_id !== $user->id) {
            abort(403, 'Only the host can start the game');
        }

        // Validate playlist exists
        if ($session->playlist_id === null) {
            abort(422, 'Cannot start game without a playlist');
        }

        // Load playlist with items
        $session->load([
            'playlist.items' => fn($query) => $query->orderBy('sort_order'),
            'scoringRule',
        ]);

        // Validate playlist has questions
        if ($session->playlist->items->isEmpty()) {
            abort(422, 'Cannot start game with empty playlist');
        }

        // Create rounds from playlist items
        $roundNumber = 1;
        foreach ($session->playlist->items as $item) {
            $round = SessionRound::create([
                'session_id' => $session->id,
                'round_number' => $roundNumber,
                'question_id' => $item->question_id,
                'started_at' => $roundNumber === 1 ? now() : null,
            ]);

            if ($roundNumber === 1 && $session->scoringRule->max_time_ms) {
                EndRound::dispatch($round->id)->delay(now()->addMilliseconds($session->scoringRule->max_time_ms));
            }

            $roundNumber++;
        }

        $session->update([
            'status' => SessionStatus::InProgress,
            'started_at' => now(),
        ]);

        $session->load([
            'host',
            'quizMode',
            'scoringRule',
            'playlist',
            'participants.user',
        ]);

        return response()->json(SessionLobbyResponse::from([
            'session' => GameSessionData::from($session),
        ]));
    }
}
