import { ApiClient } from '@/lib/apiClient';
import { Link, useLoaderData } from 'react-router-dom';
import { TracksResponse } from '@/types/effect-schemas';

export async function tracksLoader() {
    const result = await ApiClient.showTracks();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load tracks');
}

export function TracksPage() {
    const data = useLoaderData<TracksResponse>();

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold mb-2">Browse Tracks</h1>
                <p className="text-muted">Discover music tracks to use in your quizzes</p>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data.tracks.data.map((track) => (
                    <div
                        key={track.id}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <h2 className="text-xl font-semibold mb-1">
                            {track.title}
                        </h2>
                        <p className="text-muted mb-2">
                            by {track.artist_name}
                        </p>
                        {track.album_name && (
                            <p className="text-sm text-muted mb-2">
                                from {track.album_name}
                            </p>
                        )}
                        <div className="text-sm text-muted space-y-1">
                            {track.genre && <p>Genre: {track.genre}</p>}
                            {track.release_year && <p>Year: {track.release_year}</p>}
                            {track.duration_ms && (
                                <p>
                                    Duration: {Math.floor(track.duration_ms / 60000)}:
                                    {String(Math.floor((track.duration_ms % 60000) / 1000)).padStart(2, '0')}
                                </p>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {data.tracks.data.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted mb-4">
                        No tracks available at the moment.
                    </p>
                </div>
            )}
        </div>
    );
}