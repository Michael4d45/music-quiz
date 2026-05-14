<?php

declare(strict_types=1);

use App\Features\GameSessions\Actions\CreateGameSession;
use App\Features\GameSessions\Actions\JoinGameSession;
use App\Features\GameSessions\Actions\LeaveGameSession;
use App\Features\GameSessions\Actions\ListMyGameSessions;
use App\Features\GameSessions\Actions\ListPublicLobbyGameSessions;
use App\Features\GameSessions\Actions\ShowGameSessionByRoomCode;
use App\Features\GameSessions\Actions\UpdateGameSession;
use App\Features\MusicTracks\Actions\CreateMusicTrack;
use App\Features\MusicTracks\Actions\CreateMusicTrackUpload;
use App\Features\MusicTracks\Actions\DestroyMusicTrack;
use App\Features\MusicTracks\Actions\ListMyMusicTracks;
use App\Features\MusicTracks\Actions\StreamMyMusicTrackUpload;
use App\Features\MusicTracks\Actions\UpdateMusicTrack;
use App\Features\Playlists\Actions\AddPlaylistItem;
use App\Features\Playlists\Actions\CreatePlaylist;
use App\Features\Playlists\Actions\DestroyPlaylist;
use App\Features\Playlists\Actions\ListMyPlaylists;
use App\Features\Playlists\Actions\ListPlaylistItems;
use App\Features\Playlists\Actions\RemovePlaylistItem;
use App\Features\Playlists\Actions\UpdatePlaylist;
use App\Features\QuizQuestions\Actions\CreateQuizQuestion;
use App\Features\QuizQuestions\Actions\DestroyQuizQuestion;
use App\Features\QuizQuestions\Actions\ListMyQuizQuestions;
use App\Features\QuizQuestions\Actions\UpdateQuizQuestion;
use App\Features\Reference\Actions\ListMusicSources;
use App\Features\Reference\Actions\ListQuestionTypes;
use App\Features\Reference\Actions\ListQuizModes;
use App\Features\Reference\Actions\ListScoringRules;
use App\Features\Reference\Actions\ListSubCategories;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:5,1'])->group(function (): void {
    require base_path('app/Features/Auth/password-reset-routes.php');
});

Route::middleware(['throttle:60,1'])->group(function (): void {
    Route::get('game-sessions/lobby', ListPublicLobbyGameSessions::class);
});

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get(
        'game-sessions/room/{roomCode}',
        ShowGameSessionByRoomCode::class,
    )->where('roomCode', '[A-Za-z0-9]{6}');
    Route::post('game-sessions/join', JoinGameSession::class);
    Route::delete(
        'game-sessions/{gameSession}/leave',
        LeaveGameSession::class,
    )->whereUuid('gameSession');
});

Route::middleware(['web', 'registered'])->group(function (): void {
    Route::get('reference/quiz-modes', ListQuizModes::class);
    Route::get('reference/scoring-rules', ListScoringRules::class);
    Route::get('reference/sub-categories', ListSubCategories::class);
    Route::get('reference/music-sources', ListMusicSources::class);
    Route::get('reference/question-types', ListQuestionTypes::class);

    Route::get('my/playlists', ListMyPlaylists::class);
    Route::post('my/playlists', CreatePlaylist::class);
    Route::patch('my/playlists/{playlist}', UpdatePlaylist::class)->whereUuid(
        'playlist',
    );
    Route::delete('my/playlists/{playlist}', DestroyPlaylist::class)->whereUuid(
        'playlist',
    );
    Route::get(
        'my/playlists/{playlist}/items',
        ListPlaylistItems::class,
    )->whereUuid('playlist');
    Route::post(
        'my/playlists/{playlist}/items',
        AddPlaylistItem::class,
    )->whereUuid('playlist');
    Route::delete(
        'my/playlists/{playlist}/items/{playlistItem}',
        RemovePlaylistItem::class,
    )
        ->whereUuid('playlist')
        ->whereUuid('playlistItem');

    Route::get('my/quiz-questions', ListMyQuizQuestions::class);
    Route::post('my/quiz-questions', CreateQuizQuestion::class);
    Route::patch(
        'my/quiz-questions/{quizQuestion}',
        UpdateQuizQuestion::class,
    )->whereUuid('quizQuestion');
    Route::delete(
        'my/quiz-questions/{quizQuestion}',
        DestroyQuizQuestion::class,
    )->whereUuid('quizQuestion');

    Route::get('my/music-tracks', ListMyMusicTracks::class);
    Route::post('my/music-tracks', CreateMusicTrack::class);
    Route::post('my/music-tracks/upload', CreateMusicTrackUpload::class);
    Route::get(
        'my/music-tracks/{musicTrack}/audio',
        StreamMyMusicTrackUpload::class,
    )->whereUuid('musicTrack');
    Route::patch(
        'my/music-tracks/{musicTrack}',
        UpdateMusicTrack::class,
    )->whereUuid('musicTrack');
    Route::delete(
        'my/music-tracks/{musicTrack}',
        DestroyMusicTrack::class,
    )->whereUuid('musicTrack');

    Route::get('my/game-sessions', ListMyGameSessions::class);
    Route::post('my/game-sessions', CreateGameSession::class);
    Route::patch(
        'my/game-sessions/{gameSession}',
        UpdateGameSession::class,
    )->whereUuid('gameSession');

    require base_path('app/Features/Auth/routes-registered.php');
});

Route::middleware(['web'])->group(function (): void {
    require base_path('app/Features/Auth/routes-session.php');
    require base_path('app/Features/Broadcasting/routes.php');
});
