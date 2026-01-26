import { ApiClient } from '@/lib/apiClient';
import { StatisticsResponse } from '@/schemas/App/Data/Response';
import { useLoaderData } from 'react-router-dom';

export async function statisticsLoader() {
    const result = await ApiClient.showStatistics();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load statistics');
}

export function StatisticsPage() {
    const data = useLoaderData<StatisticsResponse>();

    const stats = data.statistic;
    const recentSessions = data.recent_sessions;

    return (
        <div className="container mx-auto px-4 py-8">
            <h1 className="mb-8 text-3xl font-bold">My Statistics</h1>

            {stats ? (
                <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="mb-2 text-lg font-semibold">
                            Games Played
                        </h3>
                        <p className="text-3xl font-bold text-(--primary)">
                            {stats.total_games_played}
                        </p>
                    </div>

                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="mb-2 text-lg font-semibold">Wins</h3>
                        <p className="text-3xl font-bold text-green-600">
                            {stats.total_wins}
                        </p>
                    </div>

                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="mb-2 text-lg font-semibold">
                            Total Points
                        </h3>
                        <p className="text-3xl font-bold text-(--success)">
                            {stats.total_points.toLocaleString()}
                        </p>
                    </div>

                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="mb-2 text-lg font-semibold">
                            Average Score
                        </h3>
                        <p className="text-3xl font-bold text-blue-600">
                            {stats.average_score
                                ? Math.round(stats.average_score)
                                : 0}
                        </p>
                    </div>
                </div>
            ) : (
                <div className="bg-card mb-8 rounded-lg p-6 shadow">
                    <p className="text-muted">
                        No statistics available yet. Play some games to see your
                        stats!
                    </p>
                </div>
            )}

            <div className="bg-card rounded-lg p-6 shadow">
                <h2 className="mb-4 text-xl font-semibold">Recent Games</h2>

                {recentSessions.length > 0 ? (
                    <div className="space-y-4">
                        {recentSessions.map((session) => (
                            <div
                                key={session.id}
                                className="bg-muted flex items-center justify-between rounded p-4"
                            >
                                <div>
                                    <p className="font-mono font-semibold">
                                        {session.room_code}
                                    </p>
                                    <p className="text-muted text-sm">
                                        {(session.quiz_mode as any)?.name} •{' '}
                                        {(session.participants as any)
                                            ?.length || 0}{' '}
                                        players
                                    </p>
                                </div>
                                <div className="text-right">
                                    <p className="text-muted text-sm">
                                        {session.status === 'completed'
                                            ? 'Completed'
                                            : session.status}
                                    </p>
                                    {session.ended_at && (
                                        <p className="text-muted text-xs">
                                            {new Date(
                                                session.ended_at,
                                            ).toLocaleDateString()}
                                        </p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-muted">No recent games.</p>
                )}
            </div>
        </div>
    );
}
