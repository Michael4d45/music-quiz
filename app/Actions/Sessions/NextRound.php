<?php

declare(strict_types=1);

namespace App\Actions\Sessions;

use App\Enums\SessionStatus;
use App\Events\SessionEventOccurred;
use App\Http\Requests\AuthRequest;
use App\Jobs\EndRound;
use App\Models\GameSession;
use App\Models\SessionFinalScore;
use Illuminate\Http\JsonResponse;

class NextRound
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        string $roomCode,
        AuthRequest $request,
    ): JsonResponse {
        $user = $request->assertedUser();

        $session = GameSession::query()
            ->where('room_code', $roomCode)
            ->with(['scoringRule', 'participants.answers.round', 'rounds'])
            ->firstOrFail();

        if ($session->host_id !== $user->id) {
            abort(403, 'Only the host can advance to the next round');
        }

        // Find the last round that was started (reorder to override the relation's default ordering)
        $lastStartedRound = $session
            ->rounds()
            ->whereNotNull('started_at')
            ->reorder('round_number', 'desc')
            ->first();

        // If there's an active round (started but not ended), end it
        if ($lastStartedRound && !$lastStartedRound->ended_at) {
            $lastStartedRound->update(['ended_at' => now()]);
        }

        // Find next unstarted round (the next with no started_at)
        $nextRound = $session
            ->rounds()
            ->whereNull('started_at')
            ->orderBy('round_number', 'asc')
            ->first();

        if ($nextRound) {
            $nextRound->update(['started_at' => now()]);

            if ($session->scoringRule->max_time_ms) {
                EndRound::dispatch($nextRound->id)->delay(now()->addMilliseconds($session->scoringRule->max_time_ms));
            }

            broadcast(new SessionEventOccurred($session, 'RoundStarted', [
                'round_number' => $nextRound->round_number,
                'question_id' => $nextRound->question_id,
            ]))->toOthers();

            return response()->json([
                'message' => 'Advanced to next round',
                'round_number' => $nextRound->round_number,
                'status' => 'InProgress',
            ]);
        }

        // No more rounds, end the game
        $session->update([
            'status' => SessionStatus::Completed,
            'ended_at' => now(),
        ]);

        // Create final scores
        $sortedParticipants = $session
            ->participants
            ->sortByDesc('current_total_score')
            ->values();

        foreach ($sortedParticipants as $index => $participant) {
            $maxStreak = 0;
            $currentStreak = 0;
            $orderedAnswers = $participant->answers->sortBy(fn($a) => $a->round->round_number);

            foreach ($orderedAnswers as $answer) {
                if ($answer->is_correct) {
                    $currentStreak++;
                    $maxStreak = max($maxStreak, $currentStreak);
                } else {
                    $currentStreak = 0;
                }
            }

            SessionFinalScore::create([
                'session_id' => $session->id,
                'participant_id' => $participant->id,
                'final_score' => $participant->current_total_score,
                'final_rank' => $index + 1,
                'questions_answered' => $participant->answers->count(),
                'correct_answers' => $participant
                    ->answers
                    ->where('is_correct', true)
                    ->count(),
                'average_response_time_ms' => (int) $participant->answers->avg(
                    'response_time_ms',
                ),
                'longest_streak' => $maxStreak,
            ]);
        }

        broadcast(
            new SessionEventOccurred($session, 'GameCompleted', []),
        )->toOthers();

        return response()->json([
            'message' => 'Game completed',
            'status' => 'Completed',
        ]);
    }
}
