<?php

declare(strict_types=1);

use App\Features\GameSessions\Actions\ListPublicLobbyGameSessions;
use Illuminate\Support\Facades\Route;

Route::get('game-sessions/lobby', ListPublicLobbyGameSessions::class);
