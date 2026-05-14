<?php

declare(strict_types=1);

namespace App\Support\GameSessions;

use App\Data\Models\GameSessionData;
use App\Data\Responses\AnswerVariantGameplayData;
use App\Data\Responses\GameSessionRoomViewData;
use App\Data\Responses\MultipleChoiceOptionGameplayData;
use App\Data\Responses\PlayerAnswerGameplayData;
use App\Data\Responses\QuizQuestionGameplayData;
use App\Data\Responses\SessionRoundGameplayData;
use App\Models\GameSession;
use App\Models\PlayerAnswer;
use App\Models\QuizQuestion;
use App\Models\SessionRound;
use App\Models\User;

final class GameSessionRoomViewBuilder
{
    public static function build(GameSession $session, User $viewer): GameSessionRoomViewData
    {
        $roundsRelation = $session->relationLoaded('rounds')
            ? $session->getRelation('rounds')
            : collect();
        $session->unsetRelation('rounds');
        $sessionData = GameSessionData::from($session);

        $viewerIsHost = (string) $session->host_id === (string) $viewer->id;
        $viewerParticipantId = $session
            ->participants
            ->first(static fn($p) => (string) $p->user_id === (string) $viewer->id)
            ?->id;

        /** @var list<SessionRoundGameplayData> $gameplayRounds */
        $gameplayRounds = [];

        if ($roundsRelation !== null) {
            foreach ($roundsRelation as $round) {
                if (!$round instanceof SessionRound) {
                    continue;
                }

                $gameplayRounds[] = self::mapRound(
                    $round,
                    $viewerIsHost,
                    $viewerParticipantId,
                );
            }
        }

        return new GameSessionRoomViewData(
            session: $sessionData,
            rounds: $gameplayRounds,
            viewer_is_host: $viewerIsHost,
            viewer_participant_id: $viewerParticipantId,
        );
    }

    private static function mapRound(
        SessionRound $round,
        bool $viewerIsHost,
        null|string $viewerParticipantId,
    ): SessionRoundGameplayData {
        $question = $round->question;
        $roundEnded = $round->ended_at !== null;
        $revealSensitive = $viewerIsHost || $roundEnded;

        return new SessionRoundGameplayData(
            id: $round->id,
            session_id: $round->session_id,
            round_number: $round->round_number,
            question_id: $round->question_id,
            started_at: $round->started_at,
            ended_at: $round->ended_at,
            first_buzzer_id: $round->first_buzzer_id,
            question: self::mapQuestion($question, $revealSensitive),
            answers: self::mapAnswers(
                $round,
                $revealSensitive,
                $viewerIsHost,
                $viewerParticipantId,
            ),
        );
    }

    private static function mapQuestion(
        QuizQuestion $question,
        bool $revealSensitive,
    ): QuizQuestionGameplayData {
        $options = $question->multipleChoiceOptions->sortBy('sort_order')->values()->map(
            static function ($option) use ($revealSensitive): MultipleChoiceOptionGameplayData {
                return new MultipleChoiceOptionGameplayData(
                    id: $option->id,
                    question_id: $option->question_id,
                    option_text: $option->option_text,
                    sort_order: $option->sort_order,
                    is_correct: $revealSensitive ? $option->is_correct : null,
                );
            },
        )->all();

        $variants = $revealSensitive
            ? $question->answerVariants->map(
                static fn($v): AnswerVariantGameplayData => new AnswerVariantGameplayData(
                    id: $v->id,
                    accepted_text: $v->accepted_text,
                ),
            )->all()
            : [];

        return new QuizQuestionGameplayData(
            id: $question->id,
            question_type: $question->question_type,
            prompt_text: $question->prompt_text,
            correct_answer: $revealSensitive ? $question->correct_answer : null,
            base_points: $question->base_points,
            media_start_seconds: $question->media_start_seconds,
            media_end_seconds: $question->media_end_seconds,
            multiple_choice_options: $options,
            answer_variants: $variants,
        );
    }

    /**
     * @return list<PlayerAnswerGameplayData>
     */
    private static function mapAnswers(
        SessionRound $round,
        bool $revealSensitive,
        bool $viewerIsHost,
        null|string $viewerParticipantId,
    ): array {
        $answers = [];

        foreach ($round->answers as $answer) {
            if (!$answer instanceof PlayerAnswer) {
                continue;
            }

            $answers[] = self::mapAnswer(
                $answer,
                $revealSensitive,
                $viewerIsHost,
                $viewerParticipantId,
            );
        }

        return $answers;
    }

    private static function mapAnswer(
        PlayerAnswer $answer,
        bool $revealSensitive,
        bool $viewerIsHost,
        null|string $viewerParticipantId,
    ): PlayerAnswerGameplayData {
        $participant = $answer->participant;
        $displayName = $participant?->user?->name ?? 'Player';
        $isOwn =
            $viewerParticipantId !== null
            && (string) $answer->participant_id === (string) $viewerParticipantId;

        $revealThisAnswer = $viewerIsHost || $revealSensitive || $isOwn;

        return new PlayerAnswerGameplayData(
            id: $answer->id,
            round_id: $answer->round_id,
            participant_id: $answer->participant_id,
            participant_display_name: $displayName,
            submitted_text: $revealThisAnswer ? $answer->submitted_text : null,
            selected_option_id: $revealThisAnswer ? $answer->selected_option_id : null,
            is_correct: $revealThisAnswer ? $answer->is_correct : null,
            points_awarded: $revealThisAnswer ? $answer->points_awarded : null,
        );
    }
}
