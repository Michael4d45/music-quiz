import { ApiClient } from '@/lib/apiClientSingleton';
import { useAuth } from '@/contexts/AuthContext';
import { Link, useLoaderData } from 'react-router-dom';
import { HomeResponse } from '@/types/effect-schemas';

export async function homeLoader() {
    const result = await ApiClient.showHome();
    if (result._tag === 'Success') {
        return result.data;
    }
    console.error(result);
    // Return empty data on error (e.g., not authenticated)
    return {
        statistic: null,
        recent_sessions: [],
        recent_playlists: [],
    } as HomeResponse;
}

export function HomePage() {
    const { user } = useAuth();
    const data = useLoaderData<HomeResponse>();
    const isGuest = user?.is_guest ?? false;

    if (!user || isGuest) {
        return (
            <div className="mx-auto max-w-7xl">
                <div className="flex h-screen flex-col items-center justify-center text-center">
                    <h1 className="text-4xl">Welcome to Music Quiz</h1>
                    <p className="mt-4 text-lg text-muted">
                        Test your music knowledge with fun quiz games
                    </p>
                    <div className="mt-8 flex gap-4">
                        <Link
                            to="/login"
                            className="btn-primary px-6 py-3"
                        >
                            Log in
                        </Link>
                        <Link
                            to="/register"
                            className="btn-secondary px-6 py-3"
                        >
                            Sign up
                        </Link>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="mx-auto max-w-7xl">
            <div className="mb-8">
                <h1>Welcome back, {user.name}!</h1>
                {data.statistic && (
                    <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="card p-4">
                            <p className="text-sm text-muted">
                                Games Played
                            </p>
                            <p className="text-2xl font-bold text-secondary">
                                {data.statistic.total_games_played}
                            </p>
                        </div>
                        <div className="card p-4">
                            <p className="text-sm text-muted">
                                Total Points
                            </p>
                            <p className="text-2xl font-bold text-secondary">
                                {data.statistic.total_points}
                            </p>
                        </div>
                        <div className="card p-4">
                            <p className="text-sm text-muted">
                                Best Streak
                            </p>
                            <p className="text-2xl font-bold text-secondary">
                                {data.statistic.best_streak}
                            </p>
                        </div>
                    </div>
                )}
            </div>

            <div className="mb-8 flex gap-4">
                <Link to="/sessions/create" className="btn-primary px-6 py-3">
                    Create Game
                </Link>
                <Link to="/sessions/join" className="btn-secondary px-6 py-3">
                    Join Game
                </Link>
                <Link
                    to="/playlists/create"
                    className="btn-secondary px-6 py-3"
                >
                    Create Playlist
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div>
                    <h2 className="mb-4">Recent Game Sessions</h2>
                    {data.recent_sessions.length === 0 ? (
                        <div className="card">
                            <p className="text-muted">
                                No recent game sessions
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {data.recent_sessions.map((session) => (
                                <Link
                                    key={session.id}
                                    to={`/sessions/${session.room_code}`}
                                    className="card block p-4 transition hover:shadow-lg"
                                >
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium text-secondary">
                                                Room: {session.room_code}
                                            </p>
                                            <p className="text-sm text-muted">
                                                {session.status}
                                            </p>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>

                <div>
                    <h2 className="mb-4">Recent Playlists</h2>
                    {data.recent_playlists.length === 0 ? (
                        <div className="card">
                            <p className="text-muted">
                                No playlists yet
                            </p>
                            <Link
                                to="/playlists/create"
                                className="mt-4 inline-block text-sm"
                            >
                                Create your first playlist
                            </Link>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {data.recent_playlists.map((playlist) => (
                                <Link
                                    key={playlist.id}
                                    to={`/playlists/${playlist.id}`}
                                    className="card block p-4 transition hover:shadow-lg"
                                >
                                    <p className="font-medium text-gray-900 dark:text-white">
                                        {playlist.name}
                                    </p>
                                    {playlist.description && (
                                        <p className="mt-1 text-sm text-muted">
                                            {playlist.description}
                                        </p>
                                    )}
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}