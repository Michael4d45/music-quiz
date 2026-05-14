import { createBrowserRouter } from 'react-router-dom';
import { App } from './app';
import { authRoutes } from './features/auth/routes';
import { ErrorPage } from './features/ErrorPage';
import {
    GameSessionRoomPage,
    gameSessionRecapLoader,
    gameSessionRoomLoader,
} from './features/game-sessions/GameSessionRoomPage';
import {
    GameSessionsLobbyPage,
    gameSessionsLobbyLoader,
} from './features/game-sessions/GameSessionsLobbyPage';
import {
    MyGameSessionsPage,
    myGameSessionsLoader,
} from './features/game-sessions/MyGameSessionsPage';
import { HomePage } from './features/home/HomePage';
import {
    MyMusicTracksPage,
    myMusicTracksLoader,
} from './features/music-tracks/MyMusicTracksPage';
import { NotFoundPage } from './features/NotFoundPage';
import { myPlaylistsLoader } from './features/playlists/myPlaylistsLoader';
import { MyPlaylistsPage } from './features/playlists/MyPlaylistsPage';
import {
    PlaylistDetailPage,
    playlistDetailLoader,
} from './features/playlists/PlaylistDetailPage';
import { ProfilePage, profileLoader } from './features/profile/ProfilePage';
import {
    MyQuizQuestionsPage,
    myQuizQuestionsLoader,
} from './features/quiz-questions/MyQuizQuestionsPage';

function RouterHydrateFallback() {
    return (
        <div className="bg-primary text-muted flex min-h-dvh items-center justify-center p-6 text-sm">
            Loading…
        </div>
    );
}

export const router = createBrowserRouter([
    {
        path: '/',
        element: <App />,
        errorElement: <ErrorPage />,
        HydrateFallback: RouterHydrateFallback,
        children: [
            {
                index: true,
                element: <HomePage />,
            },
            {
                path: 'game-sessions/lobby',
                element: <GameSessionsLobbyPage />,
                loader: gameSessionsLobbyLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'game-sessions/room/:roomCode',
                element: <GameSessionRoomPage />,
                loader: gameSessionRoomLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: false },
            },
            {
                path: 'my/game-sessions/:sessionId/recap',
                element: <GameSessionRoomPage />,
                loader: gameSessionRecapLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            {
                path: 'my/game-sessions',
                element: <MyGameSessionsPage />,
                loader: myGameSessionsLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            {
                path: 'my/playlists',
                element: <MyPlaylistsPage />,
                loader: myPlaylistsLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            {
                path: 'my/playlists/:playlistId',
                element: <PlaylistDetailPage />,
                loader: playlistDetailLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            {
                path: 'my/quiz-questions',
                element: <MyQuizQuestionsPage />,
                loader: myQuizQuestionsLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            {
                path: 'my/music-tracks',
                element: <MyMusicTracksPage />,
                loader: myMusicTracksLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            {
                path: 'profile',
                element: <ProfilePage />,
                loader: profileLoader,
                errorElement: <ErrorPage />,
                handle: { isProtected: true, requiresFullAccount: true },
            },
            ...authRoutes,
        ],
    },
    {
        path: '*',
        element: <NotFoundPage />,
    },
]);
