import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useLoaderData, useParams, Link } from 'react-router-dom';
import { PlaylistResponse } from '@/types/effect-schemas';

export async function playlistDetailLoader({ params }: any) {
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

    const result = await ApiClient.showPlaylist(params.id);
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load playlist');
}

export function PlaylistDetailPage() {
    const data = useLoaderData<PlaylistResponse>();
    const { id } = useParams<{ id: string }>();
    const playlist = data.playlist;

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8">
                <Link
                    to="/playlists"
                    className="text-(--primary) hover:underline mb-4 inline-block"
                >
                    ← Back to Playlists
                </Link>
                <h1 className="text-3xl font-bold">{playlist.name}</h1>
                {playlist.description && (
                    <p className="text-muted mt-2">{playlist.description}</p>
                )}
                <div className="flex items-center gap-4 mt-4 text-sm text-muted">
                    <span>Created by: {(playlist.user as any)?.name || 'Unknown'}</span>
                    <span>{playlist.is_public ? 'Public' : 'Private'}</span>
                    <span>{playlist.play_count} plays</span>
                </div>
            </div>

            <div className="bg-card rounded-lg p-6 shadow">
                <h2 className="text-xl font-semibold mb-4">Questions ({(playlist.items as any)?.length || 0})</h2>

                {!(playlist.items as any)?.length ? (
                    <div className="text-center py-8">
                        <p className="text-muted">No questions in this playlist yet.</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {(playlist.items as any)?.map((item: any, index: number) => (
                            <div key={item.id} className="border rounded-lg p-4">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2 mb-2">
                                            <span className="bg-muted text-xs px-2 py-1 rounded">
                                                Question {index + 1}
                                            </span>
                                            <span className="bg-muted text-xs px-2 py-1 rounded">
                                                {(item.question as any).question_type}
                                            </span>
                                            <span className="bg-muted text-xs px-2 py-1 rounded">
                                                {(item.question as any).base_points} points
                                            </span>
                                        </div>

                                        {(item.question as any).prompt_text && (
                                            <p className="text-sm mb-2">{(item.question as any).prompt_text}</p>
                                        )}

                                        <p className="font-medium">
                                            Answer: {(item.question as any).correct_answer}
                                        </p>

                                        {(item.question as any).track && (
                                            <p className="text-sm text-muted mt-2">
                                                From: {(item.question as any).track.title} by {(item.question as any).track.artist_name}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}