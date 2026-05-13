<?php

declare(strict_types=1);

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (
    User $user,
    string $id,
): bool {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('game-session.{sessionId}', function (
    User $user,
    string $sessionId,
): bool {
    $session = GameSession::query()->find($sessionId);

    if (!$session instanceof GameSession) {
        return false;
    }

    if ((string) $session->host_id === (string) $user->id) {
        return true;
    }

    return $session->participants()->where('user_id', $user->id)->exists();
});

Broadcast::channel('online', function (User $user): array {
    return [
        'id' => (string) $user->id,
        'name' => $user->name ?? 'Unknown user',
    ];
});
