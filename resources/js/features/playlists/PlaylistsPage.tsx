import { ApiClient } from '@/lib/apiClient';
import { Link, useLoaderData } from 'react-router-dom';

export async function playlistsLoader() {
    const result = await ApiClient.listPlaylists();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load playlists');
}

export function PlaylistsPage() {
    const data = useLoaderData();

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8 flex items-center justify-between">
                <h1 className="text-3xl font-bold">My Playlists</h1>
                <Link
                    to="/playlists/create"
                    className="btn-info rounded-lg px-4 py-2 transition-colors"
                >
                    Create Playlist
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data.playlists.map((playlist) => (
                    <Link
                        key={playlist.id}
                        to={`/playlists/${playlist.id}`}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <h2 className="text-xl font-semibold">
                            {playlist.name}
                        </h2>
                        {playlist.description && (
                            <p className="text-muted mt-2 line-clamp-2">
                                {playlist.description}
                            </p>
                        )}
                        <div className="text-muted mt-4 flex items-center gap-4 text-sm">
                            <span>
                                {playlist.is_public ? 'Public' : 'Private'}
                            </span>
                            <span>{playlist.play_count} plays</span>
                        </div>
                    </Link>
                ))}
            </div>

            {data.playlists.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted mb-4">
                        You don't have any playlists yet.
                    </p>
                    <Link
                        to="/playlists/create"
                        className="text-(--info) hover:underline"
                    >
                        Create your first playlist
                    </Link>
                </div>
            )}
        </div>
    );
}
