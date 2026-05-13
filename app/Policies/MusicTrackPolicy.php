<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MusicTrack;
use App\Models\User;

class MusicTrackPolicy
{
    public function viewAny(User $user): bool
    {
        return !$user->is_guest;
    }

    public function view(User $user, MusicTrack $musicTrack): bool
    {
        if ($user->is_guest) {
            return false;
        }

        if ($musicTrack->user_id === null) {
            return true;
        }

        return $musicTrack->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return !$user->is_guest;
    }

    public function update(User $user, MusicTrack $musicTrack): bool
    {
        return !$user->is_guest
            && $musicTrack->user_id !== null
            && $musicTrack->user_id === $user->id;
    }

    public function delete(User $user, MusicTrack $musicTrack): bool
    {
        return !$user->is_guest
            && $musicTrack->user_id !== null
            && $musicTrack->user_id === $user->id;
    }

    public function restore(User $user, MusicTrack $musicTrack): bool
    {
        return false;
    }

    public function forceDelete(User $user, MusicTrack $musicTrack): bool
    {
        return false;
    }
}
