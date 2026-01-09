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
                            <th className="px-6 py-3 text-center text-xs font-medium tracking-wider text-muted uppercase">
                                Rank
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase">
                                Player
                            </th>
                            <th className="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Total Points
                            </th>
                            <th className="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Games
                            </th>
                            <th className="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">
                                Wins
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-secondary">
                        {data.players.map((player, index) => (
                            <tr key={player.id}>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="flex items-center justify-center">
                                        <span
                                            className={`inline-flex h-8 w-8 items-start justify-center rounded-full font-semibold pt-[0.155rem] pr-[0.01rem]                                                
                                                ${index === 0
                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'
                                                    : index === 1
                                                        ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                        : index === 2
                                                            ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                                                            : 'bg-secondary-bg text-muted'
                                                }`}
                                        >
                                            {index + 1}
                                        </span>
                                    </div>
                                </td>

                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className="font-medium">
                                        {player.user?.name || 'Anonymous'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="flex items-center justify-center font-semibold">
                                        {player.total_points.toLocaleString()}
                                    </div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="flex items-center justify-center">
                                        {player.total_games_played}
                                    </div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="flex items-center justify-center">
                                        {player.total_wins}
                                    </div>
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
