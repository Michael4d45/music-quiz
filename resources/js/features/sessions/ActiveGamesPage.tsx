import { ApiClient } from '@/lib/apiClient';
import { Link, useLoaderData } from 'react-router-dom';

export async function activeGamesLoader() {
    const result = await ApiClient.listActiveGames();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load active games');
}

export function ActiveGamesPage() {
    const data = useLoaderData();

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8 flex items-center justify-between">
                <h1 className="text-3xl font-bold">Active Games</h1>
                <Link
                    to="/sessions/create"
                    className="btn-success rounded-lg px-4 py-2 transition-colors"
                >
                    Create Game
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data.sessions.map((session) => (
                    <Link
                        key={session.id}
                        to={`/sessions/${session.room_code}`}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <div className="flex items-start justify-between">
                            <h2 className="font-mono text-2xl font-bold">
                                {session.room_code}
                            </h2>
                            <span
                                className={`rounded px-2 py-1 text-xs ${
                                    session.status === 'lobby'
                                        ? 'badge-warning'
                                        : 'badge-success'
                                }`}
                            >
                                {session.status}
                            </span>
                        </div>
                        <div className="text-muted mt-4 text-sm">
                            <p>Max Players: {session.max_players}</p>
                        </div>
                    </Link>
                ))}
            </div>

            {data.sessions.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted mb-4">
                        You don't have any active games.
                    </p>
                    <Link
                        to="/sessions/create"
                        className="text-(--success) hover:underline"
                    >
                        Create a new game
                    </Link>
                </div>
            )}
        </div>
    );
}
