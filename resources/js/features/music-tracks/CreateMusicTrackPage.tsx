import SearchSelect from '@/components/Select/SearchSelect';
import { ApiClient } from '@/lib/apiClient';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

export async function createMusicTrackLoader() {
    return {};
}

export function CreateMusicTrackPage() {
    const navigate = useNavigate();
    const [isCreating, setIsCreating] = useState(false);
    const [subCategories, setSubCategories] = useState<
        { value: string; label: string }[]
    >([]);
    const [musicSources, setMusicSources] = useState<
        { value: string; label: string }[]
    >([]);
    const [loading, setLoading] = useState(true);

    const [errors, setErrors] = useState<Record<string, string[]>>({});

    useEffect(() => {
        const loadData = async () => {
            try {
                const [subCategoriesResult, musicSourcesResult] =
                    await Promise.all([
                        ApiClient.showSubCategories(),
                        ApiClient.showMusicSources(),
                    ]);

                if (subCategoriesResult._tag === 'Success') {
                    const options = subCategoriesResult.data.sub_categories.map(
                        (cat) => ({
                            value: cat.id.toString(),
                            label: cat.name,
                        }),
                    );
                    setSubCategories(options);
                }

                if (musicSourcesResult._tag === 'Success') {
                    const options = musicSourcesResult.data.music_sources.map(
                        (source) => ({
                            value: source.id.toString(),
                            label: source.name,
                        }),
                    );
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
                album_name: (formData.get('album_name') as string) || null,
                release_year: formData.get('release_year')
                    ? parseInt(formData.get('release_year') as string)
                    : null,
                genre: (formData.get('genre') as string) || null,
                duration_ms: formData.get('duration_ms')
                    ? parseInt(formData.get('duration_ms') as string)
                    : null,
            };

            const result = await ApiClient.createMusicTrack(payload);

            if (result._tag === 'Success') {
                navigate('/music-tracks');
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
                <h1 className="mb-8 text-center text-3xl font-bold">
                    Add Music Track
                </h1>

                <form
                    onSubmit={handleSubmit}
                    className="bg-card space-y-4 rounded-lg p-6 shadow-lg"
                >
                    <div>
                        <label
                            htmlFor="title"
                            className="mb-2 block text-sm font-medium"
                        >
                            Title *
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            required
                        />
                        {errors.title && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.title[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="artist_name"
                            className="mb-2 block text-sm font-medium"
                        >
                            Artist Name *
                        </label>
                        <input
                            type="text"
                            id="artist_name"
                            name="artist_name"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            required
                        />
                        {errors.artist_name && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.artist_name[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium">
                            Category *
                        </label>
                        <SearchSelect
                            name="sub_category_id"
                            placeholder="Select a category"
                            options={subCategories}
                        />
                        {errors.sub_category_id && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.sub_category_id[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium">
                            Music Source *
                        </label>
                        <SearchSelect
                            name="primary_source_id"
                            placeholder="Select a music source"
                            options={musicSources}
                        />
                        {errors.primary_source_id && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.primary_source_id[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="album_name"
                            className="mb-2 block text-sm font-medium"
                        >
                            Album Name
                        </label>
                        <input
                            type="text"
                            id="album_name"
                            name="album_name"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                        />
                        {errors.album_name && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.album_name[0]}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                htmlFor="release_year"
                                className="mb-2 block text-sm font-medium"
                            >
                                Release Year
                            </label>
                            <input
                                type="number"
                                id="release_year"
                                name="release_year"
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            />
                            {errors.release_year && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.release_year[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="genre"
                                className="mb-2 block text-sm font-medium"
                            >
                                Genre
                            </label>
                            <input
                                type="text"
                                id="genre"
                                name="genre"
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            />
                            {errors.genre && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.genre[0]}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <label
                            htmlFor="duration_ms"
                            className="mb-2 block text-sm font-medium"
                        >
                            Duration (milliseconds)
                        </label>
                        <input
                            type="number"
                            id="duration_ms"
                            name="duration_ms"
                            min="1"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                        />
                        {errors.duration_ms && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.duration_ms[0]}
                            </p>
                        )}
                    </div>

                    <div className="flex gap-4 pt-4">
                        <button
                            type="button"
                            onClick={() => navigate('/music-tracks')}
                            className="btn-secondary flex-1 rounded-lg py-3 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={isCreating}
                            className="btn-success flex-1 rounded-lg py-3 transition-colors disabled:opacity-50"
                        >
                            {isCreating ? 'Adding...' : 'Add Track'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
