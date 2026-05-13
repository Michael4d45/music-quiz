import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import {
    createMusicTrack,
    fetchMyMusicTracks,
} from '@/features/music-tracks/api';
import { fetchMusicSources, fetchSubCategories } from '@/features/reference/api';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { MyMusicTracksResponseData } from '@/schemas/App/Data/Responses/MyMusicTracksResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export interface MyMusicTracksLoaderData extends MyMusicTracksResponseData {
    readonly sub_categories: readonly IdLabelOptionData[];
    readonly music_sources: readonly IdLabelOptionData[];
}

export async function myMusicTracksLoader(): Promise<MyMusicTracksLoaderData> {
    const [tracksRes, subRes, srcRes] = await Promise.all([
        fetchMyMusicTracks(),
        fetchSubCategories(),
        fetchMusicSources(),
    ]);

    return {
        tracks: tracksRes._tag === 'Success' ? tracksRes.data.tracks : [],
        sub_categories:
            subRes._tag === 'Success' ? subRes.data.sub_categories : [],
        music_sources:
            srcRes._tag === 'Success' ? srcRes.data.music_sources : [],
    };
}

export function MyMusicTracksPage() {
    const { tracks, sub_categories, music_sources } =
        useLoaderData<MyMusicTracksLoaderData>();
    const revalidator = useRevalidator();
    const [title, setTitle] = useState('');
    const [artist, setArtist] = useState('');
    const [albumName, setAlbumName] = useState('');
    const [releaseYear, setReleaseYear] = useState('');
    const [genre, setGenre] = useState('');
    const [durationSeconds, setDurationSeconds] = useState('');
    const [subCategoryId, setSubCategoryId] = useState('');
    const [sourceId, setSourceId] = useState('');

    const handleCreate = async () => {
        if (!title.trim() || !artist.trim()) {
            toast.error('Title and artist are required');
            return;
        }
        if (!subCategoryId || !sourceId) {
            toast.error('Choose a genre category and a music source');
            return;
        }

        const yearParsed = releaseYear.trim()
            ? Number.parseInt(releaseYear.trim(), 10)
            : null;
        const durationMs =
            durationSeconds.trim() !== ''
                ? Math.round(
                      Number.parseFloat(durationSeconds.trim()) * 1000,
                  )
                : null;

        if (
            releaseYear.trim() !== '' &&
            (yearParsed === null || Number.isNaN(yearParsed))
        ) {
            toast.error('Release year must be a whole number');
            return;
        }

        if (
            durationSeconds.trim() !== '' &&
            (durationMs === null || Number.isNaN(durationMs) || durationMs < 0)
        ) {
            toast.error('Duration must be a positive number of seconds');
            return;
        }

        const result = await createMusicTrack({
            title: title.trim(),
            artist_name: artist.trim(),
            album_name: albumName.trim() || null,
            release_year: yearParsed,
            genre: genre.trim() || null,
            duration_ms: durationMs,
            sub_category_id: subCategoryId,
            primary_source_id: sourceId,
        });
        if (result._tag === 'Success') {
            toast.success('Track created');
            setTitle('');
            setArtist('');
            setAlbumName('');
            setReleaseYear('');
            setGenre('');
            setDurationSeconds('');
            setSubCategoryId('');
            setSourceId('');
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
                    My quiz questions
                </ButtonLink>
            </div>

            <p className="text-muted mb-6 max-w-2xl text-sm">
                Tracks are the songs your quiz questions can point at. Pick the
                style bucket that fits the song, then choose which streaming
                catalog entry this row represents (for example Spotify or Deezer).
            </p>

            <div className="bg-card mb-8 flex flex-col gap-4 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <h2 className="text-lg font-semibold">Add a track</h2>
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Title
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                            autoComplete="off"
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Artist
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={artist}
                            onChange={(e) => setArtist(e.target.value)}
                            autoComplete="off"
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Style / sub-genre
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={subCategoryId}
                            onChange={(e) => setSubCategoryId(e.target.value)}
                        >
                            <option value="">Select a category…</option>
                            {sub_categories.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Primary catalog source
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={sourceId}
                            onChange={(e) => setSourceId(e.target.value)}
                        >
                            <option value="">Select a source…</option>
                            {music_sources.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Album (optional)
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={albumName}
                            onChange={(e) => setAlbumName(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Release year (optional)
                        </label>
                        <input
                            type="number"
                            min={1800}
                            max={2100}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={releaseYear}
                            onChange={(e) => setReleaseYear(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Genre label (optional)
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={genre}
                            onChange={(e) => setGenre(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Duration in seconds (optional)
                        </label>
                        <input
                            type="number"
                            min={0}
                            step="0.1"
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={durationSeconds}
                            onChange={(e) => setDurationSeconds(e.target.value)}
                        />
                    </div>
                </div>
                <Button type="button" onClick={() => void handleCreate()}>
                    Create track
                </Button>
            </div>

            {tracks.length === 0 ? (
                <p className="text-muted">
                    No tracks yet. Add one above, then link it when you build
                    questions.
                </p>
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
                            <div className="text-muted mt-1 text-sm">
                                {t.album_name
                                    ? `${t.album_name} · `
                                    : ''}
                                {t.release_year != null
                                    ? `${t.release_year} · `
                                    : ''}
                                {t.genre ?? 'No genre tag'}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
