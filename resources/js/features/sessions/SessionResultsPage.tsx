import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { SessionResultsResponse } from '@/schemas/App/Data/Response';
import { useLoaderData } from 'react-router-dom';

export async function sessionResultsLoader({ params }: any) {
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

    const result = await ApiClient.showSessionResults(params.roomCode);
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load session results');
}

export function SessionResultsPage() {
    const data = useLoaderData<SessionResultsResponse>();

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-3xl">
                <div className="mb-12 text-center">
                    <h1 className="mb-2 text-4xl font-bold">Game Results</h1>
                    <p className="text-muted">Final Rankings and Statistics</p>
                </div>

                <div className="space-y-6">
                    {data.final_scores?.map((score: any, index: number) => (
                        <div
                            key={score.id}
                            className={`bg-card flex items-center justify-between rounded-lg border-l-4 p-6 shadow-lg ${
                                index === 0
                                    ? 'border-yellow-400'
                                    : index === 1
                                      ? 'border-gray-400'
                                      : index === 2
                                        ? 'border-amber-600'
                                        : 'border-transparent'
                            }`}
                        >
                            <div className="flex items-center gap-4">
                                <div className="text-muted w-8 text-3xl font-bold">
                                    {score.final_rank}
                                </div>
                                <div>
                                    <div className="text-xl font-bold">
                                        {score.participant?.user?.name ||
                                            score.participant?.guest_name}
                                    </div>
                                    <div className="text-muted text-sm">
                                        {score.correct_answers} /{' '}
                                        {score.questions_answered} correct •
                                        Avg.{' '}
                                        {(
                                            score.average_response_time_ms /
                                            1000
                                        ).toFixed(2)}
                                        s
                                    </div>
                                </div>
                            </div>
                            <div className="font-mono text-3xl font-bold">
                                {score.final_score}
                            </div>
                        </div>
                    ))}
                </div>

                <div className="mt-12">
                    <h2 className="mb-6 text-2xl font-bold">Round History</h2>
                    <div className="bg-card overflow-hidden rounded-lg shadow-lg">
                        <table className="w-full">
                            <thead className="bg-muted">
                                <tr>
                                    <th className="px-6 py-3 text-left">
                                        Round
                                    </th>
                                    <th className="px-6 py-3 text-left">
                                        Question
                                    </th>
                                    <th className="px-6 py-3 text-left">
                                        Answer
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-border divide-y">
                                {data.rounds.map((round) => (
                                    <tr key={round.id}>
                                        <td className="px-6 py-4">
                                            {round.round_number}
                                        </td>
                                        <td className="px-6 py-4">
                                            {round.question?.prompt_text ||
                                                'Identify track/artist'}
                                        </td>
                                        <td className="px-6 py-4 font-medium">
                                            {round.question?.correct_answer}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="mt-12 flex justify-center">
                    <a
                        href="/active-games"
                        className="btn-primary rounded-lg px-8 py-3"
                    >
                        Back to My Games
                    </a>
                </div>
            </div>
        </div>
    );
}
