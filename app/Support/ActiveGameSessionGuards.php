<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\SessionParticipant;
use App\Models\User;

/**
 * Session rules: registered users may be in several active games at once and may host
 * multiple non-completed sessions concurrently.
 * Guests may only be a participant in one non-completed game at a time; guests cannot host
 * (see GameSessionPolicy::create and JoinGameSession).
 */
final class ActiveGameSessionGuards
{
    /**
     * Whether a guest is already a participant in a different non-completed session
     * than {@see $session}. Always false for registered users.
     */
    public static function guestHasCommitmentOutsideSession(
        User $user,
        GameSession $session,
    ): bool {
        if (!$user->is_guest) {
            return false;
        }

        return self::guestHasParticipantCommitmentExcluding(
            $user,
            $session->id,
        );
    }

    private static function guestHasParticipantCommitmentExcluding(
        User $user,
        null|string $exceptSessionId,
    ): bool {
        $query = SessionParticipant::query()
            ->where('user_id', $user->id)
            ->whereRelation(
                'session',
                'status',
                '!=',
                SessionStatus::Completed,
            );

        if ($exceptSessionId !== null) {
            $query->where('session_id', '!=', $exceptSessionId);
        }

        return $query->exists();
    }
}
