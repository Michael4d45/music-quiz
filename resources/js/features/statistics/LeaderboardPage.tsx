import { ApiClient } from '@/lib/apiClientSingleton';
import { useLoaderData } from 'react-router-dom';

export async function leaderboardLoader() {
    const result = await ApiClient.showLeaderboard();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load leaderboard');
}

export function LeaderboardPage() {
    const data = useLoaderData();

    return (
        <div className="container mx-auto px-4 py-8">
            <h1 className="mb-8 text-3xl font-bold">Leaderboard</h1>

            <div className="overflow-hidden rounded-lg bg-card shadow">
                <table className="min-w-full divide-y divide-secondary">
                    <thead className="bg-secondary-bg">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase">
                                Rank
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase">
                                Player
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Total Points
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Games
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Wins
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-secondary">
                        {data.players.map((player, index) => (
                            <tr key={player.id}>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span
                                        className={`inline-flex h-8 w-8 items-center justify-center rounded-full ${
                                            index === 0
                                                ? 'badge-warning'
                                                : index === 1
                                                  ? 'bg-secondary-bg text-secondary'
                                                  : index === 2
                                                    ? 'badge-warning'
                                                    : 'bg-secondary-bg text-muted'
                                        }`}
                                    >
                                        {index + 1}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className="font-medium">
                                        {player.user?.name || 'Anonymous'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-right font-semibold whitespace-nowrap">
                                    {player.total_points.toLocaleString()}
                                </td>
                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                    {player.total_games_played}
                                </td>
                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                    {player.total_wins}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {data.players.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted">
                        No players on the leaderboard yet. Be the first!
                    </p>
                </div>
            )}
        </div>
    );
}
