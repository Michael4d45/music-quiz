<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\SessionFinalScoreData;
use App\Data\Models\SessionRoundData;
use App\Data\Response\SessionResultsResponse;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;

class ShowSessionResults
{
    /**
     * Display the session results data.
     */
    public function __invoke(string $roomCode): JsonResponse
    {
        $session = GameSession::where('room_code', $roomCode)
            ->with(['finalScores.participant.user', 'rounds.question'])
            ->firstOrFail();

        return response()->json(SessionResultsResponse::from([
            'final_scores' => SessionFinalScoreData::collect($session->finalScores),
            'rounds' => SessionRoundData::collect($session->rounds),
        ]));
    }
}
