import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useLoaderData } from 'react-router-dom';
import { StatisticsResponse } from '@/schemas/App/Data/Response';

export async function statisticsLoader() {
    let user = authManager.getUser();
    let token = authManager.getToken();

    // Try to restore from session if no local token
    if (!user || !token) {
        const result = await ApiClient.fetchSessionToken();
        if (result._tag === 'Success') {
            authManager.setAuthData(result.data.token, result.data.user);
            user = result.data.user;
            token = result.data.token;
        }
    }

    if (!user || !token) {
        return redirect('/login');
    }

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
            <h1 className="text-3xl font-bold mb-8">My Statistics</h1>

            {stats ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="text-lg font-semibold mb-2">Games Played</h3>
                        <p className="text-3xl font-bold text-(--primary)">{stats.total_games_played}</p>
                    </div>

                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="text-lg font-semibold mb-2">Wins</h3>
                        <p className="text-3xl font-bold text-green-600">{stats.total_wins}</p>
                    </div>

                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="text-lg font-semibold mb-2">Total Points</h3>
                        <p className="text-3xl font-bold text-(--success)">{stats.total_points.toLocaleString()}</p>
                    </div>

                    <div className="bg-card rounded-lg p-6 shadow">
                        <h3 className="text-lg font-semibold mb-2">Average Score</h3>
                        <p className="text-3xl font-bold text-blue-600">
                            {stats.average_score ? Math.round(stats.average_score) : 0}
                        </p>
                    </div>
                </div>
            ) : (
                <div className="bg-card rounded-lg p-6 shadow mb-8">
                    <p className="text-muted">No statistics available yet. Play some games to see your stats!</p>
                </div>
            )}

            <div className="bg-card rounded-lg p-6 shadow">
                <h2 className="text-xl font-semibold mb-4">Recent Games</h2>

                {recentSessions.length > 0 ? (
                    <div className="space-y-4">
                        {recentSessions.map((session) => (
                            <div key={session.id} className="flex items-center justify-between p-4 bg-muted rounded">
                                <div>
                                    <p className="font-mono font-semibold">{session.room_code}</p>
                                    <p className="text-sm text-muted">
                                        {(session.quiz_mode as any)?.name} • {(session.participants as any)?.length || 0} players
                                    </p>
                                </div>
                                <div className="text-right">
                                    <p className="text-sm text-muted">
                                        {session.status === 'completed' ? 'Completed' : session.status}
                                    </p>
                                    {session.ended_at && (
                                        <p className="text-xs text-muted">
                                            {new Date(session.ended_at).toLocaleDateString()}
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