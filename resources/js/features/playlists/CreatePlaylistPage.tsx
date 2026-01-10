import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useNavigate } from 'react-router-dom';
import { useState } from 'react';
import { CreatePlaylistRequestSchema } from '@/types/effect-schemas';

export async function createPlaylistLoader() {
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

    return {};
}

export function CreatePlaylistPage() {
    const navigate = useNavigate();
    const [isCreating, setIsCreating] = useState(false);
    const [errors, setErrors] = useState<Record<string, string[]>>({});

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setIsCreating(true);
        setErrors({});

        try {
            const formData = new FormData(e.currentTarget);

            const result = await ApiClient.createPlaylist({
                name: formData.get('name') as string,
                description: formData.get('description') as string || null,
                is_public: formData.has('is_public'),
                question_ids: [],
                new_questions: [],
            });

            if (result._tag === 'Success') {
                navigate('/playlists');
            } else if (result._tag === 'ValidationError') {
                setErrors(Object.fromEntries(
                    Object.entries(result.errors).map(([key, value]) => [key, [...value]])
                ));
            } else {
                console.error('Failed to create playlist:', result);
            }
        } catch (error) {
            console.error('Error creating playlist:', error);
        } finally {
            setIsCreating(false);
        }
    };

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-md">
                <h1 className="text-3xl font-bold mb-8 text-center">Create New Playlist</h1>

                <form onSubmit={handleSubmit} className="bg-card rounded-lg p-6 shadow-lg">
                    <div className="mb-4">
                        <label htmlFor="name" className="block text-sm font-medium mb-2">
                            Playlist Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            placeholder="My Awesome Playlist"
                            required
                        />
                        {errors.name && (
                            <p className="text-red-500 text-sm mt-1">{errors.name[0]}</p>
                        )}
                    </div>

                    <div className="mb-4">
                        <label htmlFor="description" className="block text-sm font-medium mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            placeholder="Optional description..."
                            rows={3}
                        />
                        {errors.description && (
                            <p className="text-red-500 text-sm mt-1">{errors.description[0]}</p>
                        )}
                    </div>

                    <div className="mb-6">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="is_public"
                                className="mr-2"
                            />
                            <span className="text-sm">Make playlist public</span>
                        </label>
                        {errors.is_public && (
                            <p className="text-red-500 text-sm mt-1">{errors.is_public[0]}</p>
                        )}
                    </div>

                    <div className="flex gap-4">
                        <button
                            type="button"
                            onClick={() => navigate('/playlists')}
                            className="flex-1 btn-secondary rounded-lg py-3 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={isCreating}
                            className="flex-1 btn-success rounded-lg py-3 transition-colors disabled:opacity-50"
                        >
                            {isCreating ? 'Creating...' : 'Create Playlist'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}