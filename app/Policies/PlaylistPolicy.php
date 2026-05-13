<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;

class PlaylistPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->is_guest;
    }

    public function view(User $user, Playlist $playlist): bool
    {
        return !$user->is_guest && $playlist->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return !$user->is_guest;
    }

    public function update(User $user, Playlist $playlist): bool
    {
        return !$user->is_guest && $playlist->user_id === $user->id;
    }

    public function delete(User $user, Playlist $playlist): bool
    {
        return !$user->is_guest && $playlist->user_id === $user->id;
    }

    public function restore(User $user, Playlist $playlist): bool
    {
        return false;
    }

    public function forceDelete(User $user, Playlist $playlist): bool
    {
        return false;
    }
}
