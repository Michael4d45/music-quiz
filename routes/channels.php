<?php

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (
    User $user,
    string $id,
): bool {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('online', function (User $user): array {
    return [
        'id' => (string) $user->id,
        'name' => $user->name ?? 'Unknown user',
    ];
});

Broadcast::channel('session.{roomCode}', function ($user, $roomCode) {
    // Users can listen to session channels if they are participants
    $session = GameSession::where('room_code', $roomCode)->first();

    if (!$session) {
        return false;
    }

    return $session->participants()->where('user_id', $user->id)->exists();
});
