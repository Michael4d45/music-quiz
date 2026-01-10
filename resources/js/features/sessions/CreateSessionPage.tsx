import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useNavigate, useLoaderData } from 'react-router-dom';
import { useState } from 'react';
import { CreateSessionRequestSchema } from '@/types/effect-schemas';

export async function createSessionLoader() {
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

    // Fetch available quiz modes, scoring rules, and user playlists
    try {
        const [quizModesResult, scoringRulesResult, playlistsResult] = await Promise.all([
            ApiClient.showQuizModes(),
            ApiClient.showScoringRules(),
            ApiClient.showUserPlaylists(),
        ]);

        return {
            quizModes: quizModesResult._tag === 'Success' ? quizModesResult.data.quiz_modes : [],
            scoringRules: scoringRulesResult._tag === 'Success' ? scoringRulesResult.data.scoring_rules : [],
            playlists: playlistsResult._tag === 'Success' ? playlistsResult.data.playlists : [],
        };
    } catch (error) {
        console.error('Error loading session data:', error);
        return {
            quizModes: [],
            scoringRules: [],
            playlists: [],
        };
    }
}

export function CreateSessionPage() {
    const navigate = useNavigate();
    const loaderData = useLoaderData();
    const [isCreating, setIsCreating] = useState(false);

    const quizModes = loaderData?.quizModes || [];
    const scoringRules = loaderData?.scoringRules || [];
    const playlists = loaderData?.playlists || [];

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setIsCreating(true);
        try {
            const formData = new FormData(e.currentTarget);

            const payload = {
                quiz_mode_id: formData.get('quiz_mode_id') as string,
                scoring_rule_id: formData.get('scoring_rule_id') as string,
                playlist_id: formData.get('playlist_id') as string || null,
                max_players: parseInt(formData.get('max_players') as string) || 10,
            };

            const result = await ApiClient.createSession(payload);
            if (result._tag === 'Success') {
                navigate(`/sessions/${result.data.session.room_code}`);
            } else {
                console.error('Failed to create session:', result);
                // Handle error
            }
        } catch (error) {
            console.error('Error creating session:', error);
        } finally {
            setIsCreating(false);
        }
    };

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-md">
                <h1 className="text-3xl font-bold mb-8 text-center">Create New Game</h1>

                <form onSubmit={handleSubmit} className="bg-card rounded-lg p-6 shadow-lg">
                    <div className="mb-4">
                        <label htmlFor="max_players" className="block text-sm font-medium mb-2">
                            Max Players
                        </label>
                        <input
                            type="number"
                            id="max_players"
                            name="max_players"
                            min="1"
                            max="50"
                            defaultValue={10}
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                        />
                    </div>

                    <div className="mb-6">
                        <label htmlFor="quiz_mode_id" className="block text-sm font-medium mb-2">
                            Quiz Mode *
                        </label>
                        <select
                            id="quiz_mode_id"
                            name="quiz_mode_id"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            required
                        >
                            <option value="">Select a quiz mode</option>
                            {quizModes.map((mode: any) => (
                                <option key={mode.id} value={mode.id}>
                                    {mode.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="mb-6">
                        <label htmlFor="scoring_rule_id" className="block text-sm font-medium mb-2">
                            Scoring Rule *
                        </label>
                        <select
                            id="scoring_rule_id"
                            name="scoring_rule_id"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            required
                        >
                            <option value="">Select a scoring rule</option>
                            {scoringRules.map((rule: any) => (
                                <option key={rule.id} value={rule.id}>
                                    {rule.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="mb-6">
                        <label htmlFor="playlist_id" className="block text-sm font-medium mb-2">
                            Playlist (Optional)
                        </label>
                        <select
                            id="playlist_id"
                            name="playlist_id"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                        >
                            <option value="">No playlist</option>
                            {playlists.map((playlist: any) => (
                                <option key={playlist.id} value={playlist.id}>
                                    {playlist.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <button
                        type="submit"
                        disabled={isCreating}
                        className="w-full btn-success rounded-lg py-3 transition-colors disabled:opacity-50"
                    >
                        {isCreating ? 'Creating...' : 'Create Game'}
                    </button>
                </form>
            </div>
        </div>
    );
}