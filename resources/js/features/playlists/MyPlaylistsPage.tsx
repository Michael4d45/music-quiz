import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { fetchMyPlaylists } from '@/features/playlists/api';
import type { MyPlaylistsResponseData } from '@/schemas/App/Data/Responses/MyPlaylistsResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { Link, useLoaderData, useRevalidator } from 'react-router-dom';

export async function myPlaylistsLoader(): Promise<MyPlaylistsResponseData> {
    const result = await fetchMyPlaylists();
    if (result._tag === 'Success') {
        return result.data;
    }
    return { playlists: [] };
}

export function MyPlaylistsPage() {
    const { playlists } = useLoaderData<MyPlaylistsResponseData>();
    const revalidator = useRevalidator();
    const [name, setName] = useState('');

    const handleCreate = async () => {
        if (!name.trim()) {
            toast.error('Enter a name');
            return;
        }
        const { createPlaylist } = await import('@/features/playlists/api');
        const result = await createPlaylist({ name: name.trim() });
        if (result._tag === 'Success') {
            toast.success('Playlist created');
            setName('');
            revalidator.revalidate();
        } else {
            toast.error('Could not create playlist');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">My playlists</h1>
                <ButtonLink to="/my/quiz-questions" variant="secondary">
                    Manage questions
                </ButtonLink>
            </div>

            <p className="text-muted mb-6 max-w-2xl text-sm">
                A playlist is an ordered set of quiz questions. Open a playlist to
                add or remove questions, then attach it when you host a session so
                rounds draw from that set.
            </p>

            <div className="bg-card mb-8 flex flex-col gap-3 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10 sm:flex-row sm:items-end">
                <div className="grow">
                    <label
                        htmlFor="new-playlist-name"
                        className="text-muted mb-1 block text-sm font-medium"
                    >
                        New playlist name
                    </label>
                    <input
                        id="new-playlist-name"
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                    />
                </div>
                <Button type="button" onClick={() => void handleCreate()}>
                    Create
                </Button>
            </div>

            {playlists.length === 0 ? (
                <p className="text-muted">No playlists yet.</p>
            ) : (
                <ul className="flex flex-col gap-3" role="list">
                    {playlists.map((p) => (
                        <li key={p.id}>
                            <Link
                                to={`/my/playlists/${p.id}`}
                                className="bg-card hover:border-primary/40 flex flex-col gap-1 rounded-lg border border-transparent p-4 shadow-md transition-colors dark:border-white/10"
                            >
                                <span className="font-semibold">{p.name}</span>
                                <span className="text-muted text-sm">
                                    {p.status} · {p.visibility}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
