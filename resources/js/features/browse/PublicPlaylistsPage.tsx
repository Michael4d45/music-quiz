import { ApiClient } from '@/lib/apiClient';
import { PlaylistsResponse } from '@/schemas/App/Data/Response';
import { useLoaderData } from 'react-router-dom';

export async function publicPlaylistsLoader() {
    const result = await ApiClient.showPublicPlaylists();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load public playlists');
}

export function PublicPlaylistsPage() {
    const data = useLoaderData<PlaylistsResponse>();

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8">
                <h1 className="mb-2 text-3xl font-bold">Public Playlists</h1>
                <p className="text-muted">
                    Explore community-created playlists
                </p>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data.playlists.map((playlist) => (
                    <div
                        key={playlist.id}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <h2 className="mb-2 text-xl font-semibold">
                            {playlist.name}
                        </h2>
                        {playlist.description && (
                            <p className="text-muted mb-3 line-clamp-2">
                                {playlist.description}
                            </p>
                        )}
                        <div className="text-muted space-y-1 text-sm">
                            <p>
                                Created by:{' '}
                                {(playlist.user as any)?.name || 'Unknown'}
                            </p>
                            <p>{playlist.play_count} plays</p>
                            <div className="mt-2 flex items-center gap-2">
                                <span className="bg-muted rounded px-2 py-1 text-xs">
                                    Public
                                </span>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {data.playlists.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted mb-4">
                        No public playlists available yet.
                    </p>
                </div>
            )}
        </div>
    );
}
