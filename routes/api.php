<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->group(function () {});

// Home endpoint (authenticated, but returns empty data for guests)
Route::get('home', \App\Actions\Home\ShowHome::class);

// Browse endpoints (public)
Route::prefix('browse')->group(function () {
    Route::get('/', \App\Actions\Browse\ShowBrowse::class);
    Route::get('categories', \App\Actions\Browse\ShowCategories::class);
    Route::get('categories/{id}', \App\Actions\Browse\ShowCategory::class);
    Route::get('tracks', \App\Actions\Browse\ShowTracks::class);
    Route::get('tracks/{id}', \App\Actions\Browse\ShowTrack::class);
    Route::get('playlists', \App\Actions\Browse\ShowPublicPlaylists::class);
});

// Leaderboard (public)
Route::get('leaderboard', \App\Actions\Statistics\ShowLeaderboard::class);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Playlists endpoints
    Route::prefix('playlists')->group(function () {
        Route::get('/', \App\Actions\Playlists\ShowPlaylists::class);
        Route::post('/', \App\Actions\Playlists\CreatePlaylist::class);
        Route::get('/{id}', \App\Actions\Playlists\ShowPlaylist::class);
        Route::put('/{id}', \App\Actions\Playlists\UpdatePlaylist::class);
    });

    // Music tracks endpoints
    Route::prefix('music-tracks')->group(function () {
        Route::get('/', \App\Actions\MusicTracks\ShowMusicTracks::class);
        Route::post('/', \App\Actions\MusicTracks\CreateMusicTrack::class);
    });

    // Quiz questions endpoints
    Route::prefix('quiz-questions')->group(function () {
        Route::get('/', \App\Actions\QuizQuestions\ShowQuizQuestions::class);
        Route::post('/', \App\Actions\QuizQuestions\CreateQuizQuestion::class);
    });

    // Game sessions endpoints
    Route::prefix('sessions')->group(function () {
        Route::get(
            'active-games',
            \App\Actions\Sessions\ShowActiveGames::class,
        );
        Route::post('/', \App\Actions\Sessions\CreateSession::class);
        Route::post('join', \App\Actions\Sessions\JoinSession::class);
        Route::get('{roomCode}', \App\Actions\Sessions\ShowSessionLobby::class);
        Route::post('{roomCode}/start', \App\Actions\Sessions\StartGame::class);
        Route::post(
            '{roomCode}/leave',
            \App\Actions\Sessions\LeaveSession::class,
        );
        Route::get(
            '{roomCode}/play',
            \App\Actions\Sessions\ShowSessionPlay::class,
        );
        Route::get(
            '{roomCode}/results',
            \App\Actions\Sessions\ShowSessionResults::class,
        );
    });

    // Statistics endpoints
    Route::get('statistics', \App\Actions\Statistics\ShowStatistics::class);

    // Broadcasting authentication
    Route::post(
        'broadcasting/auth',
        \App\Actions\Broadcasting\AuthenticateBroadcasting::class,
    );
});

require __DIR__ . '/auth.php';
