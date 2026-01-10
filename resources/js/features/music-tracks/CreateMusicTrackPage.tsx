import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useNavigate } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { CreateMusicTrackRequestSchema } from '@/types/effect-schemas';
import SearchSelect from '@/components/Select/SearchSelect';

export async function createMusicTrackLoader() {
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

export function CreateMusicTrackPage() {
    const navigate = useNavigate();
    const [isCreating, setIsCreating] = useState(false);
    const [subCategories, setSubCategories] = useState<{ value: string; label: string }[]>([]);
    const [musicSources, setMusicSources] = useState<{ value: string; label: string }[]>([]);
    const [loading, setLoading] = useState(true);

    const [errors, setErrors] = useState<Record<string, string[]>>({});

    useEffect(() => {
        const loadData = async () => {
            try {
                const [subCategoriesResult, musicSourcesResult] = await Promise.all([
                    ApiClient.showSubCategories(),
                    ApiClient.showMusicSources(),
                ]);

                if (subCategoriesResult._tag === 'Success') {
                    const options = subCategoriesResult.data.sub_categories.map(cat => ({
                        value: cat.id.toString(),
                        label: cat.name,
                    }));
                    setSubCategories(options);
                }

                if (musicSourcesResult._tag === 'Success') {
                    const options = musicSourcesResult.data.music_sources.map(source => ({
                        value: source.id.toString(),
                        label: source.name,
                    }));
                    setMusicSources(options);
                }
            } catch (error) {
                console.error('Error loading form data:', error);
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, []);

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setIsCreating(true);
        setErrors({});

        try {
            const formData = new FormData(e.currentTarget);

            const payload = {
                title: formData.get('title') as string,
                artist_name: formData.get('artist_name') as string,
                sub_category_id: formData.get('sub_category_id') as string,
                primary_source_id: formData.get('primary_source_id') as string,
                album_name: formData.get('album_name') as string || null,
                release_year: formData.get('release_year') ? parseInt(formData.get('release_year') as string) : null,
                genre: formData.get('genre') as string || null,
                duration_ms: formData.get('duration_ms') ? parseInt(formData.get('duration_ms') as string) : null,
            };

            const result = await ApiClient.createMusicTrack(payload);

            if (result._tag === 'Success') {
                navigate('/music-tracks');
            } else if (result._tag === 'ValidationError') {
                setErrors(Object.fromEntries(
                    Object.entries(result.errors).map(([key, value]) => [key, [...value]])
                ));
            } else {
                console.error('Failed to create music track:', result);
            }
        } catch (error) {
            console.error('Error creating music track:', error);
        } finally {
            setIsCreating(false);
        }
    };

    if (loading) {
        return (
            <div className="container mx-auto px-4 py-8">
                <div className="text-center">Loading...</div>
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-md">
                <h1 className="text-3xl font-bold mb-8 text-center">Add Music Track</h1>

                <form onSubmit={handleSubmit} className="bg-card rounded-lg p-6 shadow-lg space-y-4">
                    <div>
                        <label htmlFor="title" className="block text-sm font-medium mb-2">
                            Title *
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            required
                        />
                        {errors.title && (
                            <p className="text-red-500 text-sm mt-1">{errors.title[0]}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="artist_name" className="block text-sm font-medium mb-2">
                            Artist Name *
                        </label>
                        <input
                            type="text"
                            id="artist_name"
                            name="artist_name"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            required
                        />
                        {errors.artist_name && (
                            <p className="text-red-500 text-sm mt-1">{errors.artist_name[0]}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-2">
                            Category *
                        </label>
                        <SearchSelect
                            name="sub_category_id"
                            placeholder="Select a category"
                            options={subCategories}
                        />
                        {errors.sub_category_id && (
                            <p className="text-red-500 text-sm mt-1">{errors.sub_category_id[0]}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-2">
                            Music Source *
                        </label>
                        <SearchSelect
                            name="primary_source_id"
                            placeholder="Select a music source"
                            options={musicSources}
                        />
                        {errors.primary_source_id && (
                            <p className="text-red-500 text-sm mt-1">{errors.primary_source_id[0]}</p>
                        )}
                    </div>

                    <div>
                        <label htmlFor="album_name" className="block text-sm font-medium mb-2">
                            Album Name
                        </label>
                        <input
                            type="text"
                            id="album_name"
                            name="album_name"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                        />
                        {errors.album_name && (
                            <p className="text-red-500 text-sm mt-1">{errors.album_name[0]}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label htmlFor="release_year" className="block text-sm font-medium mb-2">
                                Release Year
                            </label>
                            <input
                                type="number"
                                id="release_year"
                                name="release_year"
                                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            />
                            {errors.release_year && (
                                <p className="text-red-500 text-sm mt-1">{errors.release_year[0]}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="genre" className="block text-sm font-medium mb-2">
                                Genre
                            </label>
                            <input
                                type="text"
                                id="genre"
                                name="genre"
                                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                            />
                            {errors.genre && (
                                <p className="text-red-500 text-sm mt-1">{errors.genre[0]}</p>
                            )}
                        </div>
                    </div>

                    <div>
                        <label htmlFor="duration_ms" className="block text-sm font-medium mb-2">
                            Duration (milliseconds)
                        </label>
                        <input
                            type="number"
                            id="duration_ms"
                            name="duration_ms"
                            min="1"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary)"
                        />
                        {errors.duration_ms && (
                            <p className="text-red-500 text-sm mt-1">{errors.duration_ms[0]}</p>
                        )}
                    </div>

                    <div className="flex gap-4 pt-4">
                        <button
                            type="button"
                            onClick={() => navigate('/music-tracks')}
                            className="flex-1 btn-secondary rounded-lg py-3 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={isCreating}
                            className="flex-1 btn-success rounded-lg py-3 transition-colors disabled:opacity-50"
                        >
                            {isCreating ? 'Adding...' : 'Add Track'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}