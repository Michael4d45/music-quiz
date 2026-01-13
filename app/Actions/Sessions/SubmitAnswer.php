<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Data\Requests\SubmitAnswerRequest;
use App\Data\Response\SubmitAnswerResponse;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubmitAnswer
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        string $roomCode,
        SubmitAnswerRequest $request,
        Request $httpRequest,
    ): JsonResponse {
        $user = $httpRequest->user();

        $session = GameSession::where('room_code', $roomCode)->firstOrFail();

        // Find participant for this user in this session
        $participant = SessionParticipant::where('session_id', $session->id)
            ->where('user_id', $user?->id)
            ->firstOrFail();

        // Get the current active round
        /** @var SessionRound|null $currentRound */
        $currentRound = $session
            ->rounds()
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->orderBy('round_number', 'desc')
            ->first();

        if (!$currentRound) {
            abort(400, 'No active round in this session');
        }

        // Check if already answered
        $existingAnswer = PlayerAnswer::where('round_id', $currentRound->id)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existingAnswer) {
            return response()->json([
                'message' => 'Already answered this round',
            ], 409);
        }

        $submittedText = $request->answer;
        $question = $currentRound->question;

        $isCorrect = $this->checkAnswer($submittedText, $question);

        $pointsAwarded = 0;
        if ($isCorrect) {
            $pointsAwarded = $this->calculatePoints(
                $currentRound,
                $session->scoringRule,
            );

            // Update participant's total score
            $participant->increment('current_total_score', $pointsAwarded);
        }

        $answer = PlayerAnswer::create([
            'round_id' => $currentRound->id,
            'participant_id' => $participant->id,
            'submitted_text' => $submittedText,
            'selected_option_id' => $request->selected_option_id,
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
            'response_time_ms' => $currentRound->started_at
                ? (int) now()->diffInMilliseconds($currentRound->started_at)
                : 0,
        ]);

        return response()->json(SubmitAnswerResponse::from([
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
            'correct_answer' => $question->correct_answer,
        ]));
    }

    private function checkAnswer(string $submitted, $question): bool
    {
        $normalizedSubmitted = Str::lower(trim($submitted));
        $normalizedCorrect = Str::lower(trim($question->correct_answer));

        if ($normalizedSubmitted === $normalizedCorrect) {
            return true;
        }

        // Check variants
        foreach ($question->answerVariants as $variant) {
            if (
                Str::lower(trim($variant->accepted_text))
                === $normalizedSubmitted
            ) {
                return true;
            }
        }

        return false;
    }

    private function calculatePoints(SessionRound $round, $scoringRule): int
    {
        $basePoints = $scoringRule->base_points ?? 1000;

        if (
            !$round->started_at
            || !$scoringRule->decay_factor
            || !$scoringRule->max_time_ms
        ) {
            return (int) $basePoints;
        }

        $elapsedMs = now()->diffInMilliseconds($round->started_at);
        $maxTime = $scoringRule->max_time_ms;

        if ($elapsedMs >= $maxTime) {
            return (int) ($basePoints * pow($scoringRule->decay_factor, 1));
        }

        $factor = $elapsedMs / $maxTime;

        return (int) ($basePoints * pow($scoringRule->decay_factor, $factor));
    }
}
