import { createBrowserRouter } from 'react-router-dom';
import { App } from './app';
import { authRoutes } from './features/auth/routes';
import {
    GameSessionsLobbyPage,
    gameSessionsLobbyLoader,
} from './features/game-sessions/GameSessionsLobbyPage';
import { ErrorPage } from './features/ErrorPage';
import { HomePage } from './features/home/HomePage';
import { NotFoundPage } from './features/NotFoundPage';
import { ProfilePage, profileLoader } from './features/profile/ProfilePage';

export const router = createBrowserRouter([
    {
        path: '/',
        element: <App />,
        errorElement: <ErrorPage />,
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
