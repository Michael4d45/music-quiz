import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { useState } from 'react';
import { redirect, useNavigate } from 'react-router-dom';

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
                description: (formData.get('description') as string) || null,
                is_public: formData.has('is_public'),
                question_ids: [],
                new_questions: [],
            });

            if (result._tag === 'Success') {
                navigate('/playlists');
            } else if (result._tag === 'ValidationError') {
                setErrors(
                    Object.fromEntries(
                        Object.entries(result.errors).map(([key, value]) => [
                            key,
                            [...value],
                        ]),
                    ),
                );
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
                <h1 className="mb-8 text-center text-3xl font-bold">
                    Create New Playlist
                </h1>

                <form
                    onSubmit={handleSubmit}
                    className="bg-card rounded-lg p-6 shadow-lg"
                >
                    <div className="mb-4">
                        <label
                            htmlFor="name"
                            className="mb-2 block text-sm font-medium"
                        >
                            Playlist Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            placeholder="My Awesome Playlist"
                            required
                        />
                        {errors.name && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.name[0]}
                            </p>
                        )}
                    </div>

                    <div className="mb-4">
                        <label
                            htmlFor="description"
                            className="mb-2 block text-sm font-medium"
                        >
                            Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            placeholder="Optional description..."
                            rows={3}
                        />
                        {errors.description && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.description[0]}
                            </p>
                        )}
                    </div>

                    <div className="mb-6">
                        <label className="flex items-center">
                            <input
                                type="checkbox"
                                name="is_public"
                                className="mr-2"
                            />
                            <span className="text-sm">
                                Make playlist public
                            </span>
                        </label>
                        {errors.is_public && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.is_public[0]}
                            </p>
                        )}
                    </div>

                    <div className="flex gap-4">
                        <button
                            type="button"
                            onClick={() => navigate('/playlists')}
                            className="btn-secondary flex-1 rounded-lg py-3 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={isCreating}
                            className="btn-success flex-1 rounded-lg py-3 transition-colors disabled:opacity-50"
                        >
                            {isCreating ? 'Creating...' : 'Create Playlist'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
