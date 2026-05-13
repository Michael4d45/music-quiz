<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->is_guest;
    }

    public function view(User $user, GameSession $gameSession): bool
    {
        if ($user->is_guest) {
            return false;
        }

        if ($gameSession->host_id === $user->id) {
            return true;
        }

        return $gameSession->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return !$user->is_guest;
    }

    public function update(User $user, GameSession $gameSession): bool
    {
        return !$user->is_guest && $gameSession->host_id === $user->id;
    }

    public function delete(User $user, GameSession $gameSession): bool
    {
        return !$user->is_guest && $gameSession->host_id === $user->id;
    }

    public function restore(User $user, GameSession $gameSession): bool
    {
        return false;
    }

    public function forceDelete(User $user, GameSession $gameSession): bool
    {
        return false;
    }
}
