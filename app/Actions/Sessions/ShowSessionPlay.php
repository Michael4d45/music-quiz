<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Models\QuizQuestionData;
use App\Data\Models\ScoringRuleData;
use App\Data\Models\SessionParticipantData;
use App\Data\Models\SessionRoundData;
use App\Data\Response\SessionPlayResponse;
use App\Http\Requests\AuthRequest;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use Illuminate\Http\JsonResponse;

class ShowSessionPlay
{
    /**
     * Display the session play data.
     */
    public function __invoke(
        string $roomCode,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $session = GameSession::where('room_code', $roomCode)
            ->with(['rounds.question', 'scoringRule'])
            ->firstOrFail();

        $currentRound = $session
            ->rounds()
            ->whereNotNull('started_at')
            ->latest('round_number')
            ->first();
        $currentQuestion = $currentRound?->question;

        $participant = SessionParticipant::where('session_id', $session->id)
            ->where('user_id', $user->id)
            ->first();

        return response()->json(SessionPlayResponse::from([
            'round' => $currentRound
                ? SessionRoundData::from($currentRound)
                : null,
            'question' => $currentQuestion
                ? QuizQuestionData::from($currentQuestion)
                : null,
            'participant' => $participant
                ? SessionParticipantData::from($participant)
                : null,
            'scoring_rule' => ScoringRuleData::from($session->scoringRule),
        ]));
    }
}
