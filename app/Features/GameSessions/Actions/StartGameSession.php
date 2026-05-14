<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Enums\Role;
use App\Enums\SessionStatus;
use App\Events\GameSessionParticipantJoined;
use App\Events\GameSessionUpdated;
use App\Models\GameSession;
use App\Models\PlaylistItem;
use App\Models\SessionParticipant;
use App\Models\SessionRound;
use App\Support\GameSessions\GameSessionRoomViewBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class StartGameSession
{
    public function __invoke(GameSession $gameSession): Response
    {
        $user = assertedUser();
        Gate::authorize('update', $gameSession);

        if ($gameSession->status !== SessionStatus::Lobby) {
            abort(422, 'This game cannot be started in its current state.');
        }

        if ($gameSession->playlist_id === null) {
            abort(422, 'Assign a playlist before starting the game.');
        }

        $items = PlaylistItem::query()
            ->where('playlist_id', $gameSession->playlist_id)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            abort(422, 'The playlist has no questions.');
        }

        $hostSeated = false;

        DB::transaction(function () use ($gameSession, $items, &$hostSeated): void {
            $gameSession->rounds()->delete();

            $maxRounds = min(10, $items->count());
            $now = now();

            foreach ($items->take($maxRounds)->values() as $index => $item) {
                SessionRound::query()->create([
                    'session_id' => $gameSession->id,
                    'round_number' => $index + 1,
                    'question_id' => $item->question_id,
                    'started_at' => $index === 0 ? $now : null,
                    'ended_at' => null,
                    'first_buzzer_id' => null,
                ]);
            }

            $gameSession->update([
                'status' => SessionStatus::InProgress,
                'started_at' => $now,
                'ended_at' => null,
            ]);

            $hostAlreadySeated = SessionParticipant::query()
                ->where('session_id', $gameSession->id)
                ->where('user_id', $gameSession->host_id)
                ->exists();

            if (!$hostAlreadySeated) {
                $playerCount = SessionParticipant::query()
                    ->where('session_id', $gameSession->id)
                    ->count();

                if ($playerCount >= $gameSession->max_players) {
                    abort(
                        422,
                        'The room is full. Remove a player or raise max players so the host has a seat before starting.',
                    );
                }

                $hostParticipant = SessionParticipant::query()->create([
                    'session_id' => $gameSession->id,
                    'user_id' => $gameSession->host_id,
                    'guest_name' => null,
                    'role' => Role::Host,
                    'current_total_score' => 0,
                    'is_connected' => true,
                    'joined_at' => $now,
                ]);

                $hostSeated = true;
            }
        });

        $gameSession->refresh();
        $gameSession->load(self::roomEagerLoads());

        if ($hostSeated) {
            $participantCount = SessionParticipant::query()
                ->where('session_id', $gameSession->id)
                ->count();

            $hostParticipant = SessionParticipant::query()
                ->where('session_id', $gameSession->id)
                ->where('user_id', $gameSession->host_id)
                ->first();

            if (
                $hostParticipant instanceof SessionParticipant
            ) {
                event(new GameSessionParticipantJoined(
                    $gameSession,
                    $hostParticipant,
                    $participantCount,
                ));
            }
        }

        event(new GameSessionUpdated($gameSession));

        return response()->json(
            GameSessionRoomViewBuilder::build($gameSession, $user),
        );
    }

    /**
     * @return list<string|array{0: string, 1: callable}>
     */
    public static function roomEagerLoads(): array
    {
        return [
            'host:id,name,is_guest,is_admin',
            'quizMode:id,name,description,allows_host_override,requires_manual_scoring,created_at,updated_at',
            'scoringRule:id,name,base_points,decay_factor,max_time_ms,streak_bonus_enabled,streak_multiplier,created_at,updated_at',
            'playlist',
            'participants' => static function ($query): void {
                $query->orderBy('joined_at');
            },
            'participants.user:id,name,is_guest,is_admin',
            'rounds' => static function ($query): void {
                $query->orderBy('round_number');
            },
            'rounds.question.multipleChoiceOptions' => static function ($query): void {
                $query->orderBy('sort_order');
            },
            'rounds.question.answerVariants',
            'rounds.answers' => static function ($query): void {
                $query->orderBy('created_at');
            },
            'rounds.answers.participant.user:id,name,is_guest,is_admin',
        ];
    }
}
