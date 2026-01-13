import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { MusicTracksResponse } from '@/schemas/App/Data/Response';
import { Link, redirect, useLoaderData } from 'react-router-dom';

export async function musicTracksLoader() {
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

    const result = await ApiClient.listMusicTracks();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load music tracks');
}

export function MusicTracksPage() {
    const data = useLoaderData<MusicTracksResponse>();

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8 flex items-center justify-between">
                <h1 className="text-3xl font-bold">My Music Tracks</h1>
                <Link
                    to="/music-tracks/create"
                    className="btn-info rounded-lg px-4 py-2 transition-colors"
                >
                    Add Track
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data.music_tracks.data.map((track) => (
                    <div
                        key={track.id}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <h2 className="text-xl font-semibold">{track.title}</h2>
                        <p className="text-muted mt-1">
                            by {track.artist_name}
                        </p>
                        {track.album_name && (
                            <p className="text-muted mt-1">
                                from {track.album_name}
                            </p>
                        )}
                        <div className="text-muted mt-4 flex flex-wrap items-center gap-4 text-sm">
                            {track.genre && <span>{track.genre}</span>}
                            {track.release_year && (
                                <span>{track.release_year}</span>
                            )}
                            {track.duration_ms && (
                                <span>
                                    {Math.floor(track.duration_ms / 60000)}:
                                    {String(
                                        Math.floor(
                                            (track.duration_ms % 60000) / 1000,
                                        ),
                                    ).padStart(2, '0')}
                                </span>
                            )}
                        </div>
                        <div className="mt-4 flex items-center gap-2">
                            <span className="text-muted bg-muted rounded px-2 py-1 text-xs">
                                {(track.sub_category as any)?.name || 'Unknown'}
                            </span>
                        </div>
                    </div>
                ))}
            </div>

            {data.music_tracks.data.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted mb-4">
                        You don't have any music tracks yet.
                    </p>
                    <Link
                        to="/music-tracks/create"
                        className="text-(--info) hover:underline"
                    >
                        Add your first track
                    </Link>
                </div>
            )}
        </div>
    );
}
