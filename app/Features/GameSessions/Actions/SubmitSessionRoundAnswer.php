<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Enums\QuestionType;
use App\Enums\SessionStatus;
use App\Events\GameSessionUpdated;
use App\Features\GameSessions\Requests\SubmitSessionRoundAnswerRequest;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
use App\Support\GameSessions\GameSessionRoomViewBuilder;
use App\Support\GameSessions\QuizAnswerJudge;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SubmitSessionRoundAnswer
{
    public function __invoke(
        GameSession $gameSession,
        SessionRound $sessionRound,
        SubmitSessionRoundAnswerRequest $request,
    ): Response {
        $user = assertedUser();

        if ((string) $sessionRound->session_id !== (string) $gameSession->id) {
            abort(404);
        }

        if ($gameSession->status !== SessionStatus::InProgress) {
            abort(422, 'The session is not accepting answers.');
        }

        $participant = SessionParticipant::query()
            ->where('session_id', $gameSession->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant instanceof SessionParticipant) {
            abort(403, 'You are not a participant in this session.');
        }

        $activeRound = $gameSession
            ->rounds()
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->orderBy('round_number')
            ->first();

        if (
            !$activeRound instanceof SessionRound
            || (string) $activeRound->id !== (string) $sessionRound->id
        ) {
            abort(422, 'This round is not accepting answers.');
        }

        if ($sessionRound->ended_at !== null) {
            abort(422, 'This round is closed.');
        }

        $alreadyAnswered = PlayerAnswer::query()
            ->where('round_id', $sessionRound->id)
            ->where('participant_id', $participant->id)
            ->exists();

        if ($alreadyAnswered) {
            abort(422, 'You already submitted an answer for this round.');
        }

        $question = $sessionRound
            ->question()
            ->with(['multipleChoiceOptions', 'answerVariants'])
            ->firstOrFail();

        $submittedText = $request->validatedSubmittedText();
        $selectedOptionId = $request->validatedSelectedOptionId();

        if ($question->question_type === QuestionType::MultipleChoice) {
            if (
                $selectedOptionId === null
                || $selectedOptionId === ''
                || $submittedText !== null && $submittedText !== ''
            ) {
                abort(422, 'Select one option for this question.');
            }
        } else {
            if (
                $submittedText === null
                || trim($submittedText) === ''
                || $selectedOptionId !== null
            ) {
                abort(422, 'Enter a text answer for this question.');
            }
        }

        $judgment = QuizAnswerJudge::judge(
            $question,
            $submittedText,
            $selectedOptionId,
        );

        $pointsAwarded = $judgment['is_correct'] ? $question->base_points : 0;

        $responseTimeMs = null;
        if ($sessionRound->started_at !== null) {
            $responseTimeMs = (int) round(
                $sessionRound->started_at->diffInMilliseconds(now()),
            );
        }

        DB::transaction(function () use (
            $sessionRound,
            $participant,
            $judgment,
            $submittedText,
            $pointsAwarded,
            $responseTimeMs,
        ): void {
            PlayerAnswer::query()->create([
                'round_id' => $sessionRound->id,
                'participant_id' => $participant->id,
                'submitted_text' => $judgment['selected_option_id'] === null
                    ? $submittedText
                    : null,
                'selected_option_id' => $judgment['selected_option_id'],
                'matched_variant_id' => $judgment['matched_variant_id'],
                'is_correct' => $judgment['is_correct'],
                'response_time_ms' => $responseTimeMs,
                'points_awarded' => $pointsAwarded,
                'host_override' => false,
            ]);

            if ($pointsAwarded > 0) {
                $participant->increment('current_total_score', $pointsAwarded);
            }
        });

        $gameSession->refresh();
        $gameSession->load(StartGameSession::roomEagerLoads());

        event(new GameSessionUpdated($gameSession));

        return response()->json(GameSessionRoomViewBuilder::build(
            $gameSession,
            $user,
        ));
    }
}
