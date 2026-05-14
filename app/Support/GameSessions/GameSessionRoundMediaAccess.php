<?php

declare(strict_types=1);

namespace App\Support\GameSessions;

use App\Models\GameSession;
use App\Models\SessionRound;
use App\Models\User;

final class GameSessionRoundMediaAccess
{
    public static function userMayAccessRound(
        User $user,
        GameSession $session,
        SessionRound $round,
    ): bool {
        if ((string) $round->session_id !== (string) $session->id) {
            return false;
        }

        if ((string) $session->host_id === (string) $user->id) {
            return true;
        }

        return $session
            ->participants()
            ->where('user_id', $user->id)
            ->exists();
    }
}
