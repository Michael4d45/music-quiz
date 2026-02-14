<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'auth:sanctum',
])->group(function () {
    // Authenticated routes for user-specific data and actions

    // Playlists endpoints
    Route::prefix('playlists')->group(function () {
        Route::get('/', \App\Actions\Playlists\ShowPlaylists::class);
        Route::post('/', \App\Actions\Playlists\CreatePlaylist::class);
        Route::get('/{playlist}', \App\Actions\Playlists\ShowPlaylist::class);
        Route::put('/{playlist}', \App\Actions\Playlists\UpdatePlaylist::class);
        Route::get(
            'user/list',
            \App\Actions\Playlists\ShowUserPlaylists::class,
        );
    });

    // Music tracks endpoints
    Route::prefix('music-tracks')->group(function () {
        Route::get('/', \App\Actions\MusicTracks\ShowMusicTracks::class);
        Route::post('/', \App\Actions\MusicTracks\CreateMusicTrack::class);
        Route::get('user', \App\Actions\MusicTracks\ShowUserMusicTracks::class);
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
        Route::get('{roomCode}', \App\Actions\Sessions\ShowSessionLobby::class);
        Route::post('{roomCode}/start', \App\Actions\Sessions\StartGame::class);
        Route::post(
            '{roomCode}/next-round',
            \App\Actions\Sessions\NextRound::class,
        );
        Route::post(
            '{roomCode}/leave',
            \App\Actions\Sessions\LeaveSession::class,
        );
        Route::get(
            '{roomCode}/play',
            \App\Actions\Sessions\ShowSessionPlay::class,
        );
        Route::post(
            '{roomCode}/answer',
            \App\Actions\Sessions\SubmitAnswer::class,
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

Route::middleware(['web'])->group(function () {
    // Routes that allow unauthenticated access for public data
    Route::get('home', \App\Actions\Home\ShowHome::class);

    Route::prefix('browse')->group(function () {
        Route::get('', \App\Actions\Browse\ShowBrowse::class);
        Route::get('categories', \App\Actions\Browse\ShowCategories::class);
        Route::get(
            'categories/{category}',
            \App\Actions\Browse\ShowCategory::class,
        );
        Route::get('tracks', \App\Actions\Browse\ShowTracks::class);
        Route::get('tracks/{track}', \App\Actions\Browse\ShowTrack::class);
        Route::get('playlists', \App\Actions\Browse\ShowPublicPlaylists::class);
    });

    Route::get('leaderboard', \App\Actions\Statistics\ShowLeaderboard::class);
    Route::get(
        'sub-categories',
        \App\Actions\SubCategories\ShowSubCategories::class,
    );
    Route::get(
        'music-sources',
        \App\Actions\MusicSources\ShowMusicSources::class,
    );
    Route::get('quiz-modes', \App\Actions\QuizModes\ShowQuizModes::class);
    Route::get(
        'scoring-rules',
        \App\Actions\ScoringRules\ShowScoringRules::class,
    );

    Route::post('sessions/join', \App\Actions\Sessions\JoinSession::class);
});

require __DIR__ . '/auth.php';
