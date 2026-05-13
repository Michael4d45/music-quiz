<?php

declare(strict_types=1);

use App\Features\GameSessions\Actions\ListPublicLobbyGameSessions;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'throttle:5,1'])->group(function (): void {
    require base_path('app/Features/Auth/password-reset-routes.php');
});

Route::middleware(['throttle:60,1'])->group(function (): void {
    Route::get('game-sessions/lobby', ListPublicLobbyGameSessions::class);
});

Route::middleware(['web'])->group(function (): void {
    require base_path('app/Features/Auth/routes-session.php');
    require base_path('app/Features/Broadcasting/routes.php');
});

Route::middleware(['web', 'registered'])->group(function (): void {
    require base_path('app/Features/Auth/routes-registered.php');
});
