import { ApiClient } from '@/lib/apiClient';
import { Link, useLoaderData } from 'react-router-dom';

export async function browseLoader() {
    const result = await ApiClient.showBrowse();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load browse data');
}

export function BrowsePage() {
    const data = useLoaderData();

    return (
        <div className="container mx-auto px-4 py-8">
            <h1 className="mb-8 text-3xl font-bold">Browse Music</h1>

            {/* Categories */}
            <section className="mb-12">
                <h2 className="mb-4 text-2xl font-semibold">Categories</h2>
                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    {data.categories.map((category) => (
                        <Link
                            key={category.id}
                            to={`/browse/categories/${category.id}`}
                            className="bg-card rounded-lg p-4 shadow transition-shadow hover:shadow-lg"
                        >
                            <h3 className="font-medium">{category.name}</h3>
                            {category.description && (
                                <p className="mt-1 text-sm text-gray-500">
                                    {category.description}
                                </p>
                            )}
                        </Link>
                    ))}
                </div>
            </section>

            {/* Featured Playlists */}
            <section className="mb-12">
                <h2 className="mb-4 text-2xl font-semibold">
                    Featured Playlists
                </h2>
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                    {data.featured_playlists.map((playlist) => (
                        <Link
                            key={playlist.id}
                            to={`/playlists/${playlist.id}`}
                            className="bg-card rounded-lg p-4 shadow transition-shadow hover:shadow-lg"
                        >
                            <h3 className="truncate font-medium">
                                {playlist.name}
                            </h3>
                            <p className="text-muted text-sm">
                                {playlist.play_count} plays
                            </p>
                        </Link>
                    ))}
                </div>
            </section>

            {/* Recent Tracks */}
            <section>
                <h2 className="mb-4 text-2xl font-semibold">Recent Tracks</h2>
                <div className="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                    {data.recent_tracks.map((track) => (
                        <Link
                            key={track.id}
                            to={`/browse/tracks/${track.id}`}
                            className="bg-card rounded-lg p-4 shadow transition-shadow hover:shadow-lg"
                        >
                            <h3 className="truncate font-medium">
                                {track.title}
                            </h3>
                            <p className="truncate text-sm text-gray-500">
                                {track.artist_name}
                            </p>
                        </Link>
                    ))}
                </div>
            </section>
        </div>
    );
}
