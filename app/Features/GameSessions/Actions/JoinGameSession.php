<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Data\Models\SessionParticipantData;
use App\Enums\Role;
use App\Enums\SessionStatus;
use App\Events\GameSessionParticipantJoined;
use App\Features\Auth\Responses\MessageResponse;
use App\Features\GameSessions\Requests\JoinGameSessionRequest;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Support\ActiveGameSessionGuards;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Join a lobby session by room code. Registered users may participate in several active games.
 * Guest users may only be in one non-completed game at a time as a player (they cannot host).
 */
class JoinGameSession
{
    public function __invoke(JoinGameSessionRequest $request): Response
    {
        $user = assertedUser();
        $normalized = strtoupper($request->room_code);

        /** @var array{participant: SessionParticipant, is_new: bool}|null $result */
        $result = DB::transaction(function () use (
            $normalized,
            $request,
            $user,
        ) {
            /** @var GameSession|null $session */
            $session = GameSession::query()
                ->whereRaw('upper(room_code) = ?', [$normalized])
                ->lockForUpdate()
                ->first();

            if (!$session instanceof GameSession) {
                return null;
            }

            if ($session->status !== SessionStatus::Lobby) {
                abort(422, 'This game is not accepting new players.');
            }

            $existing = SessionParticipant::query()
                ->where('session_id', $session->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing instanceof SessionParticipant) {
                $existing->setRelation('session', $session);

                return [
                    'participant' => $existing,
                    'is_new' => false,
                ];
            }

            if (
                $user->is_guest
                && ActiveGameSessionGuards::guestHasCommitmentOutsideSession(
                    $user,
                    $session,
                )
            ) {
                abort(422, 'Leave your current game before joining another.');
            }

            $count = SessionParticipant::query()
                ->where('session_id', $session->id)
                ->count();

            if ($count >= $session->max_players) {
                abort(422, 'This room is full.');
            }

            $created = SessionParticipant::query()->create([
                'session_id' => $session->id,
                'user_id' => $user->id,
                'guest_name' => self::normalizedParticipantDisplayName($request->display_name),
                'role' => Role::Player,
                'current_total_score' => 0,
                'is_connected' => true,
                'joined_at' => now(),
            ]);

            $created->setRelation('session', $session);

            return [
                'participant' => $created,
                'is_new' => true,
            ];
        });

        if ($result === null) {
            return response()->json(MessageResponse::from([
                'message' => 'Room not found',
            ]), 404);
        }

        $participant = $result['participant'];
        $session = GameSession::query()->findOrFail($participant->session_id);

        if ($result['is_new']) {
            $participantCount = SessionParticipant::query()
                ->where('session_id', $session->id)
                ->count();

            event(new GameSessionParticipantJoined(
                $session,
                $participant,
                $participantCount,
            ));
        }

        $participant->load('user');

        return response()->json(SessionParticipantData::from($participant));
    }

    private static function normalizedParticipantDisplayName(null|string $displayName): null|string
    {
        if ($displayName === null) {
            return null;
        }
        $trimmed = trim($displayName);

        return $trimmed === '' ? null : $trimmed;
    }
}
