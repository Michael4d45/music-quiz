import { createBrowserRouter } from 'react-router-dom';
import { App } from './app';
import { LoginPage } from './features/auth/LoginPage';
import { RegisterPage } from './features/auth/RegisterPage';
import { BrowsePage, browseLoader } from './features/browse/BrowsePage';
import {
    CategoriesPage,
    categoriesLoader,
} from './features/browse/CategoriesPage';
import { ContentPage, contentLoader } from './features/content/ContentPage';
import { ErrorPage } from './features/ErrorPage';
import { HomePage, homeLoader } from './features/home/HomePage';
import { NotFoundPage } from './features/NotFoundPage';
import {
    PlaylistsPage,
    playlistsLoader,
} from './features/playlists/PlaylistsPage';
import { ProfilePage, profileLoader } from './features/profile/ProfilePage';
import {
    ActiveGamesPage,
    activeGamesLoader,
} from './features/sessions/ActiveGamesPage';
import {
    LeaderboardPage,
    leaderboardLoader,
} from './features/statistics/LeaderboardPage';

export const router = createBrowserRouter([
    {
        path: '/',
        element: <App />,
        children: [
            {
                index: true,
                element: <HomePage />,
                loader: homeLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'content',
                element: <ContentPage />,
                loader: contentLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'profile',
                element: <ProfilePage />,
                loader: profileLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'login',
                element: <LoginPage />,
            },
            {
                path: 'register',
                element: <RegisterPage />,
            },
            // Browse routes
            {
                path: 'browse',
                element: <BrowsePage />,
                loader: browseLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'browse/categories',
                element: <CategoriesPage />,
                loader: categoriesLoader,
                errorElement: <ErrorPage />,
            },
            // Playlists routes
            {
                path: 'playlists',
                element: <PlaylistsPage />,
                loader: playlistsLoader,
                errorElement: <ErrorPage />,
            },
            // Sessions routes
            {
                path: 'active-games',
                element: <ActiveGamesPage />,
                loader: activeGamesLoader,
                errorElement: <ErrorPage />,
            },
            // Statistics routes
            {
                path: 'leaderboard',
                element: <LeaderboardPage />,
                loader: leaderboardLoader,
                errorElement: <ErrorPage />,
            },
        ],
    },
    {
        path: '*',
        element: <NotFoundPage />,
    },
]);
