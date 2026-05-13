import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { createMusicTrack, fetchMyMusicTracks } from '@/features/music-tracks/api';
import type { MyMusicTracksResponseData } from '@/schemas/App/Data/Responses/MyMusicTracksResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export async function myMusicTracksLoader(): Promise<MyMusicTracksResponseData> {
    const result = await fetchMyMusicTracks();
    if (result._tag === 'Success') {
        return result.data;
    }
    return { tracks: [] };
}

export function MyMusicTracksPage() {
    const { tracks } = useLoaderData<MyMusicTracksResponseData>();
    const revalidator = useRevalidator();
    const [title, setTitle] = useState('');
    const [artist, setArtist] = useState('');
    const [subCategoryId, setSubCategoryId] = useState('');
    const [sourceId, setSourceId] = useState('');

    const handleCreate = async () => {
        if (!title.trim() || !artist.trim()) {
            toast.error('Title and artist are required');
            return;
        }
        if (!subCategoryId.trim() || !sourceId.trim()) {
            toast.error('Sub-category ID and primary source ID are required');
            return;
        }
        const result = await createMusicTrack({
            title: title.trim(),
            artist_name: artist.trim(),
            sub_category_id: subCategoryId.trim(),
            primary_source_id: sourceId.trim(),
        });
        if (result._tag === 'Success') {
            toast.success('Track created');
            setTitle('');
            setArtist('');
            revalidator.revalidate();
        } else {
            toast.error('Could not create track');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">My music tracks</h1>
                <ButtonLink to="/my/quiz-questions" variant="secondary">
                    Questions
                </ButtonLink>
            </div>

            <div className="bg-card mb-8 flex flex-col gap-3 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <p className="text-muted text-sm">
                    Use UUIDs from your seeded sub-categories and music sources
                    (for example from your database or Filament admin).
                </p>
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label className="text-muted mb-1 block text-sm">
                            Title
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm">
                            Artist
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={artist}
                            onChange={(e) => setArtist(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-mono">
                            sub_category_id
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 font-mono text-sm"
                            value={subCategoryId}
                            onChange={(e) => setSubCategoryId(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-mono">
                            primary_source_id
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 font-mono text-sm"
                            value={sourceId}
                            onChange={(e) => setSourceId(e.target.value)}
                        />
                    </div>
                </div>
                <Button type="button" onClick={() => void handleCreate()}>
                    Create track
                </Button>
            </div>

            {tracks.length === 0 ? (
                <p className="text-muted">No tracks yet.</p>
            ) : (
                <ul className="flex flex-col gap-2" role="list">
                    {tracks.map((t) => (
                        <li
                            key={t.id}
                            className="bg-card rounded-lg border border-transparent px-4 py-3 shadow-md dark:border-white/10"
                        >
                            <div className="font-semibold">
                                {t.title} — {t.artist_name}
                            </div>
                            <div className="text-muted font-mono text-xs">
                                {t.id}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
