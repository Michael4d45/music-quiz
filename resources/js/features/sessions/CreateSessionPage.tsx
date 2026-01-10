import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useNavigate } from 'react-router-dom';
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

    // In a real app, you'd fetch available quiz modes, scoring rules, and playlists
    return {};
}

export function CreateSessionPage() {
    const navigate = useNavigate();
    const [isCreating, setIsCreating] = useState(false);
    const [formData, setFormData] = useState({
        quiz_mode_id: '',
        scoring_rule_id: '',
        playlist_id: '',
        max_players: 10,
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        setIsCreating(true);
        try {
            const payload = {
                quiz_mode_id: formData.quiz_mode_id || 'default-quiz-mode-id', // Would be dynamic
                scoring_rule_id: formData.scoring_rule_id || 'default-scoring-rule-id', // Would be dynamic
                playlist_id: formData.playlist_id || null,
                max_players: formData.max_players,
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
                            min="1"
                            max="50"
                            value={formData.max_players}
                            onChange={(e) => setFormData(prev => ({ ...prev, max_players: parseInt(e.target.value) }))}
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                        />
                    </div>

                    <div className="mb-6">
                        <label htmlFor="playlist_id" className="block text-sm font-medium mb-2">
                            Playlist (Optional)
                        </label>
                        <select
                            id="playlist_id"
                            value={formData.playlist_id}
                            onChange={(e) => setFormData(prev => ({ ...prev, playlist_id: e.target.value }))}
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                        >
                            <option value="">No playlist</option>
                            {/* Would populate with user's playlists */}
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