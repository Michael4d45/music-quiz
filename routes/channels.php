<?php

use App\Models\GameSession;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('session.{roomCode}', function ($user, $roomCode) {
    // Users can listen to session channels if they are participants
    $session = GameSession::where('room_code', $roomCode)->first();

    if (!$session) {
        return false;
    }

    return $session->participants()->where('user_id', $user->id)->exists();
});
