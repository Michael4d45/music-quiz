<?php

use App\Actions\Auth\ConfirmPassword;
use App\Actions\Auth\CreateToken;
use App\Actions\Auth\DisconnectGoogle;
use App\Actions\Auth\GetOAuthToken;
use App\Actions\Auth\Login;
use App\Actions\Auth\Logout;
use App\Actions\Auth\Register;
use App\Actions\Auth\ResetPassword;
use App\Actions\Auth\SendEmailVerificationNotification;
use App\Actions\Auth\SendPasswordResetLink;
use App\Actions\Auth\ShowUser;
use App\Actions\Auth\UpdatePassword;
use App\Actions\Auth\VerifyEmail;
use App\Actions\Browse\ShowBrowse;
use App\Actions\Browse\ShowCategories;
use App\Actions\Browse\ShowCategory;
use App\Actions\Browse\ShowPublicPlaylists;
use App\Actions\Browse\ShowTrack;
use App\Actions\Browse\ShowTracks;
use App\Actions\Home\ShowHome;
use App\Actions\MusicTracks\CreateMusicTrack;
use App\Actions\MusicTracks\ShowMusicTracks;
use App\Actions\Playlists\CreatePlaylist;
use App\Actions\Playlists\ShowPlaylist;
use App\Actions\Playlists\ShowPlaylists;
use App\Actions\Playlists\UpdatePlaylist;
use App\Actions\QuizQuestions\CreateQuizQuestion;
use App\Actions\QuizQuestions\ShowQuizQuestions;
use App\Actions\Sessions\CreateSession;
use App\Actions\Sessions\JoinSession;
use App\Actions\Sessions\LeaveSession;
use App\Actions\Sessions\ShowActiveGames;
use App\Actions\Sessions\ShowSessionLobby;
use App\Actions\Sessions\ShowSessionPlay;
use App\Actions\Sessions\ShowSessionResults;
use App\Actions\Sessions\StartGame;
use App\Actions\Statistics\ShowLeaderboard;
use App\Actions\Statistics\ShowStatistics;
use Illuminate\Support\Facades\Route;

Route::get('/user', ShowUser::class)->middleware('auth:sanctum')->name(
    'api.user',
);

// Create token for already authenticated user (used for OAuth callbacks and session auth)
// No middleware - action handles auth check and returns JSON 401 for unauthenticated
Route::get('/token', CreateToken::class)->name('api.token');

// Retrieve OAuth token from session after successful callback (uses session, not bearer token)
Route::get('/oauth-token', GetOAuthToken::class)->middleware('web')->name(
    'api.oauth-token',
);

Route::post('/login', Login::class)->name('api.login');
Route::post('/register', Register::class)->name('api.register');
Route::post('/password-reset', ResetPassword::class)->name(
    'api.password.reset',
);
Route::post('/send-password-reset-link', SendPasswordResetLink::class)->name(
    'api.password.email',
);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/verify-email', VerifyEmail::class)->name(
        'api.verification.verify',
    );
    // Logout needs web middleware (session + CSRF) to clear Redis sessions for Filament
    Route::post('/logout', Logout::class)
        ->middleware('web')
        ->name('api.logout');
    Route::post('/confirm-password', ConfirmPassword::class)->name(
        'api.password.confirm',
    );
    Route::post('/update-password', UpdatePassword::class)->name(
        'api.password.update',
    );
    Route::post('/disconnect-google', DisconnectGoogle::class)->name(
        'api.disconnect-google',
    );
    Route::post(
        '/send-email-verification-notification',
        SendEmailVerificationNotification::class,
    )->name('api.verification.send');

    // Real-time test endpoint
    Route::post(
        '/trigger-test-event',
        App\Actions\Realtime\TriggerTestEvent::class,
    )->name('api.trigger-test-event');
});

// Content routes (no auth required for demo)
Route::get('/content', App\Actions\Content\ShowContent::class)->name(
    'api.content',
);

// ============================================================================
// Music Quiz Domain Routes
// ============================================================================

// Home route (authenticated, but returns empty data for guests)
Route::get('/home', ShowHome::class)->name('api.home');

// Browse routes (public)
Route::get('/browse', ShowBrowse::class)->name('api.browse');
Route::get('/browse/categories', ShowCategories::class)->name(
    'api.browse.categories',
);
Route::get('/browse/categories/{category}', ShowCategory::class)->name(
    'api.browse.category',
);
Route::get('/browse/tracks', ShowTracks::class)->name('api.browse.tracks');
Route::get('/browse/tracks/{track}', ShowTrack::class)->name(
    'api.browse.track',
);
Route::get('/browse/playlists', ShowPublicPlaylists::class)->name(
    'api.browse.playlists',
);

// Leaderboard (public)
Route::get('/leaderboard', ShowLeaderboard::class)->name('api.leaderboard');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Playlists
    Route::get('/playlists', ShowPlaylists::class)->name('api.playlists.index');
    Route::post('/playlists', CreatePlaylist::class)->name(
        'api.playlists.store',
    );
    Route::get('/playlists/{playlist}', ShowPlaylist::class)->name(
        'api.playlists.show',
    );
    Route::put('/playlists/{playlist}', UpdatePlaylist::class)->name(
        'api.playlists.update',
    );

    // Music Tracks
    Route::get('/music-tracks', ShowMusicTracks::class)->name(
        'api.music-tracks.index',
    );
    Route::post('/music-tracks', CreateMusicTrack::class)->name(
        'api.music-tracks.store',
    );

    // Quiz Questions
    Route::get('/quiz-questions', ShowQuizQuestions::class)->name(
        'api.quiz-questions.index',
    );
    Route::post('/quiz-questions', CreateQuizQuestion::class)->name(
        'api.quiz-questions.store',
    );

    // Game Sessions
    Route::get('/active-games', ShowActiveGames::class)->name(
        'api.active-games.index',
    );
    Route::post('/sessions', CreateSession::class)->name('api.sessions.store');
    Route::post('/sessions/join', JoinSession::class)->name(
        'api.sessions.join',
    );
    Route::get('/sessions/{roomCode}', ShowSessionLobby::class)->name(
        'api.sessions.lobby',
    );
    Route::post('/sessions/{roomCode}/start', StartGame::class)->name(
        'api.sessions.start',
    );
    Route::post('/sessions/{roomCode}/leave', LeaveSession::class)->name(
        'api.sessions.leave',
    );
    Route::get('/sessions/{roomCode}/play', ShowSessionPlay::class)->name(
        'api.sessions.play',
    );
    Route::get('/sessions/{roomCode}/results', ShowSessionResults::class)->name(
        'api.sessions.results',
    );

    // Statistics
    Route::get('/statistics', ShowStatistics::class)->name('api.statistics');
});
