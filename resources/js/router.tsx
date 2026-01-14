import { createBrowserRouter } from 'react-router-dom';
import { App } from './app';
import { ForgotPasswordPage } from './features/auth/ForgotPasswordPage';
import { LoginPage } from './features/auth/LoginPage';
import { RegisterPage } from './features/auth/RegisterPage';
import { ResetPasswordPage } from './features/auth/ResetPasswordPage';
import { BrowsePage, browseLoader } from './features/browse/BrowsePage';
import {
    CategoriesPage,
    categoriesLoader,
} from './features/browse/CategoriesPage';
import {
    PublicPlaylistsPage,
    publicPlaylistsLoader,
} from './features/browse/PublicPlaylistsPage';
import { TracksPage, tracksLoader } from './features/browse/TracksPage';
import { ContentPage, contentLoader } from './features/content/ContentPage';
import { ErrorPage } from './features/ErrorPage';
import { HomePage, homeLoader } from './features/home/HomePage';
import {
    CreateMusicTrackPage,
    createMusicTrackLoader,
} from './features/music-tracks/CreateMusicTrackPage';
import {
    MusicTracksPage,
    musicTracksLoader,
} from './features/music-tracks/MusicTracksPage';
import { NotFoundPage } from './features/NotFoundPage';
import {
    CreatePlaylistPage,
    createPlaylistLoader,
} from './features/playlists/CreatePlaylistPage';
import {
    PlaylistDetailPage,
    playlistDetailLoader,
} from './features/playlists/PlaylistDetailPage';
import {
    PlaylistsPage,
    playlistsLoader,
} from './features/playlists/PlaylistsPage';
import { ProfilePage, profileLoader } from './features/profile/ProfilePage';
import {
    CreateQuizQuestionPage,
    createQuizQuestionLoader,
} from './features/quiz-questions/CreateQuizQuestionPage';
import {
    QuizQuestionsPage,
    quizQuestionsLoader,
} from './features/quiz-questions/QuizQuestionsPage';
import {
    ActiveGamesPage,
    activeGamesLoader,
} from './features/sessions/ActiveGamesPage';
import {
    CreateSessionPage,
    createSessionLoader,
} from './features/sessions/CreateSessionPage';
import {
    JoinSessionPage,
    joinSessionLoader,
} from './features/sessions/JoinSessionPage';
import {
    SessionLobbyPage,
    sessionLobbyLoader,
} from './features/sessions/SessionLobbyPage';
import {
    SessionPlayPage,
    sessionPlayLoader,
} from './features/sessions/SessionPlayPage';
import {
    SessionResultsPage,
    sessionResultsLoader,
} from './features/sessions/SessionResultsPage';
import {
    LeaderboardPage,
    leaderboardLoader,
} from './features/statistics/LeaderboardPage';
import {
    StatisticsPage,
    statisticsLoader,
} from './features/statistics/StatisticsPage';

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
            {
                path: 'browse/tracks',
                element: <TracksPage />,
                loader: tracksLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'browse/playlists',
                element: <PublicPlaylistsPage />,
                loader: publicPlaylistsLoader,
                errorElement: <ErrorPage />,
            },
            // Playlists routes
            {
                path: 'playlists',
                element: <PlaylistsPage />,
                loader: playlistsLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'playlists/:id',
                element: <PlaylistDetailPage />,
                loader: playlistDetailLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'playlists/create',
                element: <CreatePlaylistPage />,
                loader: createPlaylistLoader,
                errorElement: <ErrorPage />,
            },
            // Music tracks routes
            {
                path: 'music-tracks',
                element: <MusicTracksPage />,
                loader: musicTracksLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'music-tracks/create',
                element: <CreateMusicTrackPage />,
                loader: createMusicTrackLoader,
                errorElement: <ErrorPage />,
            },
            // Quiz questions routes
            {
                path: 'quiz-questions',
                element: <QuizQuestionsPage />,
                loader: quizQuestionsLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'quiz-questions/create',
                element: <CreateQuizQuestionPage />,
                loader: createQuizQuestionLoader,
                errorElement: <ErrorPage />,
            },
            // Sessions routes
            {
                path: 'active-games',
                element: <ActiveGamesPage />,
                loader: activeGamesLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'sessions/create',
                element: <CreateSessionPage />,
                loader: createSessionLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'sessions/join',
                element: <JoinSessionPage />,
                loader: joinSessionLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'sessions/:roomCode',
                element: <SessionLobbyPage />,
                loader: sessionLobbyLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'sessions/:roomCode/play',
                element: <SessionPlayPage />,
                loader: sessionPlayLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'sessions/:roomCode/results',
                element: <SessionResultsPage />,
                loader: sessionResultsLoader,
                errorElement: <ErrorPage />,
            },
            // Statistics routes
            {
                path: 'leaderboard',
                element: <LeaderboardPage />,
                loader: leaderboardLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'statistics',
                element: <StatisticsPage />,
                loader: statisticsLoader,
                errorElement: <ErrorPage />,
            },
            {
                path: 'forgot-password',
                element: <ForgotPasswordPage />,
            },
            {
                path: 'reset-password/:email/:token',
                element: <ResetPasswordPage />,
            },
        ],
    },
    {
        path: '*',
        element: <NotFoundPage />,
    },
]);
