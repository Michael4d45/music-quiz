import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import ConfirmModal from '@/components/ConfirmModal';
import {
    createMusicTrack,
    deleteMusicTrack,
    fetchMyMusicTracks,
    updateMusicTrack,
    uploadMusicTrack,
} from '@/features/music-tracks/api';
import { TrackUploadAudioPlayer } from '@/features/music-tracks/TrackUploadAudioPlayer';
import {
    SINGLES_GROUP,
    groupHeading,
    sortGroupEntries,
    usesAlbumForGrouping,
} from '@/features/music-tracks/trackGrouping';
import { fetchMusicSources, fetchSubCategories } from '@/features/reference/api';
import { cn } from '@/lib/utils';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import type { MyMusicTracksResponseData } from '@/schemas/App/Data/Responses/MyMusicTracksResponseData';
import {
    MusicTrackOriginKind,
    type MusicTrackOriginKind as MusicTrackOriginKindValue,
} from '@/schemas/App/Enums/MusicTrackOriginKind';
import { useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export interface MyMusicTracksLoaderData extends MyMusicTracksResponseData {
    readonly sub_categories: readonly IdLabelOptionData[];
    readonly music_sources: readonly IdLabelOptionData[];
}

const ORIGIN_OPTIONS: { value: MusicTrackOriginKindValue; label: string }[] = [
    { value: MusicTrackOriginKind.Album, label: 'Studio album' },
    { value: MusicTrackOriginKind.SoundtrackGame, label: 'Video game' },
    { value: MusicTrackOriginKind.SoundtrackFilm, label: 'Film' },
    { value: MusicTrackOriginKind.SoundtrackTv, label: 'TV show' },
    { value: MusicTrackOriginKind.OtherMedia, label: 'Other media / compilation' },
];

const ORIGIN_LABELS: Record<MusicTrackOriginKindValue, string> = {
    [MusicTrackOriginKind.Album]: 'Album',
    [MusicTrackOriginKind.SoundtrackGame]: 'Video game',
    [MusicTrackOriginKind.SoundtrackFilm]: 'Film',
    [MusicTrackOriginKind.SoundtrackTv]: 'TV show',
    [MusicTrackOriginKind.OtherMedia]: 'Other media',
};

function originSummary(track: MusicTrackData): string | null {
    if (usesAlbumForGrouping(track.origin_kind)) {
        return null;
    }
    const label = track.origin_kind
        ? ORIGIN_LABELS[track.origin_kind]
        : 'Media';
    const work = track.origin_title?.trim();
    const album = track.album_name?.trim();
    if (work) {
        return `${label}: ${work}`;
    }
    if (album) {
        return `${label} · ${album}`;
    }
    return label;
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

    const [search, setSearch] = useState('');
    const [addMode, setAddMode] = useState<'streaming' | 'upload'>('streaming');
    const [title, setTitle] = useState('');
    const [artist, setArtist] = useState('');
    const [albumName, setAlbumName] = useState('');
    const [releaseYear, setReleaseYear] = useState('');
    const [genre, setGenre] = useState('');
    const [durationSeconds, setDurationSeconds] = useState('');
    const [subCategoryId, setSubCategoryId] = useState('');
    const [sourceId, setSourceId] = useState('');
    const [originKind, setOriginKind] = useState<'' | MusicTrackOriginKindValue>(
        '',
    );
    const [originTitle, setOriginTitle] = useState('');
    const [audioFile, setAudioFile] = useState<File | null>(null);

    const [deleteTargetId, setDeleteTargetId] = useState<string | null>(null);

    const filteredTracks = (() => {
        const q = search.trim().toLowerCase();
        if (!q) {
            return tracks;
        }
        return tracks.filter((t) => {
            const blob = [
                t.title,
                t.artist_name,
                t.album_name ?? '',
                t.origin_title ?? '',
                t.genre ?? '',
            ]
                .join(' ')
                .toLowerCase();
            return blob.includes(q);
        });
    })();

    const grouped = (() => {
        const map = new Map<string, MusicTrackData[]>();
        for (const t of filteredTracks) {
            const g = groupHeading(t);
            const list = map.get(g) ?? [];
            list.push(t);
            map.set(g, list);
        }
        for (const list of map.values()) {
            list.sort((a, b) =>
                `${a.artist_name} ${a.title}`.localeCompare(
                    `${b.artist_name} ${b.title}`,
                ),
            );
        }
        return sortGroupEntries([...map.entries()]);
    })();

    const resetAddForm = () => {
        setTitle('');
        setArtist('');
        setAlbumName('');
        setReleaseYear('');
        setGenre('');
        setDurationSeconds('');
        setSubCategoryId('');
        setSourceId('');
        setOriginKind('');
        setOriginTitle('');
        setAudioFile(null);
    };

    const parseOptionalNumbers = (): {
        yearParsed: number | null;
        durationMs: number | null;
    } | null => {
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
            return null;
        }

        if (
            durationSeconds.trim() !== '' &&
            (durationMs === null || Number.isNaN(durationMs) || durationMs < 0)
        ) {
            toast.error('Duration must be a positive number of seconds');
            return null;
        }

        return { yearParsed, durationMs };
    };

    const handleCreate = async () => {
        if (!title.trim() || !artist.trim()) {
            toast.error('Title and artist are required');
            return;
        }
        if (!subCategoryId) {
            toast.error('Choose a style category');
            return;
        }

        const nums = parseOptionalNumbers();
        if (!nums) {
            return;
        }

        const albumish =
            originKind === '' || originKind === MusicTrackOriginKind.Album;

        const basePayload = {
            title: title.trim(),
            artist_name: artist.trim(),
            album_name: albumName.trim() || null,
            release_year: nums.yearParsed,
            genre: genre.trim() || null,
            duration_ms: nums.durationMs,
            sub_category_id: subCategoryId,
            origin_kind: originKind === '' ? null : originKind,
            origin_title: albumish ? null : originTitle.trim() || null,
        };

        if (addMode === 'streaming') {
            if (!sourceId) {
                toast.error('Choose a streaming catalog');
                return;
            }
            const result = await createMusicTrack({
                ...basePayload,
                primary_source_id: sourceId,
            });
            if (result._tag === 'Success') {
                toast.success('Track added');
                resetAddForm();
                revalidator.revalidate();
            } else {
                toast.error('Could not create track');
            }
            return;
        }

        if (!audioFile) {
            toast.error('Choose an audio file to upload');
            return;
        }

        const result = await uploadMusicTrack(basePayload, audioFile);
        if (result._tag === 'Success') {
            toast.success('Track uploaded');
            resetAddForm();
            revalidator.revalidate();
        } else {
            toast.error('Could not upload track');
        }
    };

    const handleConfirmDelete = async () => {
        if (!deleteTargetId) {
            return;
        }
        const result = await deleteMusicTrack(deleteTargetId);
        if (result._tag === 'Success') {
            toast.success('Track removed');
            revalidator.revalidate();
        } else {
            toast.error('Could not delete track');
        }
        setDeleteTargetId(null);
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
                Pick a collection type: normal albums group under the album
                name; games, films, and shows group under one work title, with
                an optional separate soundtrack or release title when you need
                it. Use streaming sources for catalog rows, or upload audio you
                keep locally. Uploaded tracks can be previewed here in the
                browser; catalog-only rows open in your music app to listen.
            </p>

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label className="flex min-w-0 flex-1 flex-col gap-1 text-sm">
                    <span className="text-muted font-medium">Search</span>
                    <input
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Title, artist, album, or collection…"
                        autoComplete="off"
                    />
                </label>
            </div>

            <div className="mb-8 flex flex-col gap-4">
                <h2 className="text-lg font-semibold">Library</h2>
                {grouped.length === 0 ? (
                    <p className="text-muted">
                        {tracks.length === 0
                            ? 'No tracks yet. Add your first one below.'
                            : 'No tracks match your search.'}
                    </p>
                ) : (
                    <div className="flex flex-col gap-3">
                        {grouped.map(([heading, list]) => (
                            <details
                                key={heading}
                                className="bg-card rounded-lg border border-transparent shadow-md open:border-transparent dark:border-white/10"
                                open={heading !== SINGLES_GROUP}
                            >
                                <summary className="cursor-pointer list-none rounded-lg px-4 py-3 marker:hidden [&::-webkit-details-marker]:hidden">
                                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                                        <span className="font-semibold">
                                            {heading}
                                        </span>
                                        <span className="text-muted text-sm">
                                            {list.length}{' '}
                                            {list.length === 1 ? 'track' : 'tracks'}
                                        </span>
                                    </div>
                                </summary>
                                <ul
                                    className="flex flex-col gap-2 border-t border-transparent px-2 pb-3 pt-1 dark:border-white/10"
                                    role="list"
                                >
                                    {list.map((t) => (
                                        <li key={t.id}>
                                            <TrackRow
                                                key={`${t.id}-${t.updated_at?.toString() ?? ''}`}
                                                track={t}
                                                sub_categories={sub_categories}
                                                music_sources={music_sources}
                                                onRequestDelete={() =>
                                                    setDeleteTargetId(t.id)
                                                }
                                                onSaved={() =>
                                                    revalidator.revalidate()
                                                }
                                            />
                                        </li>
                                    ))}
                                </ul>
                            </details>
                        ))}
                    </div>
                )}
            </div>

            <details className="bg-card mb-8 rounded-lg border border-transparent shadow-md dark:border-white/10">
                <summary className="cursor-pointer list-none px-4 py-3 text-lg font-semibold marker:hidden [&::-webkit-details-marker]:hidden">
                    Add a track
                </summary>
                <div className="flex flex-col gap-4 border-t border-transparent px-4 pb-4 pt-3 dark:border-white/10">
                    <fieldset className="flex flex-wrap gap-4">
                        <legend className="text-muted mb-2 text-sm font-medium">
                            How is this track stored?
                        </legend>
                        <label className="flex cursor-pointer items-center gap-2 text-sm">
                            <input
                                type="radio"
                                name="add-mode"
                                checked={addMode === 'streaming'}
                                onChange={() => setAddMode('streaming')}
                            />
                            Streaming catalog
                        </label>
                        <label className="flex cursor-pointer items-center gap-2 text-sm">
                            <input
                                type="radio"
                                name="add-mode"
                                checked={addMode === 'upload'}
                                onChange={() => setAddMode('upload')}
                            />
                            Audio file on my device
                        </label>
                    </fieldset>

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
                                onChange={(e) =>
                                    setSubCategoryId(e.target.value)
                                }
                            >
                                <option value="">Select a category…</option>
                                {sub_categories.map((opt) => (
                                    <option key={opt.id} value={opt.id}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        {addMode === 'streaming' ? (
                            <div className="sm:col-span-2">
                                <label className="text-muted mb-1 block text-sm font-medium">
                                    Primary streaming catalog
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
                        ) : (
                            <div className="sm:col-span-2">
                                <label className="text-muted mb-1 block text-sm font-medium">
                                    Audio file
                                </label>
                                <input
                                    type="file"
                                    accept="audio/mpeg,audio/wav,audio/mp4,audio/flac,audio/ogg,.mp3,.wav,.m4a,.flac,.ogg"
                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-secondary file:px-3 file:py-1 file:text-sm"
                                    onChange={(e) => {
                                        const f = e.target.files?.[0];
                                        setAudioFile(f ?? null);
                                    }}
                                />
                                <p className="text-muted mt-1 text-xs">
                                    MP3, WAV, M4A, FLAC, or OGG. Stored privately
                                    on the server for your account.
                                </p>
                            </div>
                        )}
                        <div className="sm:col-span-2">
                            <details className="rounded-md border border-dashed border-transparent p-2 dark:border-white/15">
                                <summary className="text-muted cursor-pointer text-sm font-medium">
                                    Grouping &amp; extra details (optional)
                                </summary>
                                <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                    <div className="sm:col-span-2">
                                        <label className="text-muted mb-1 block text-sm font-medium">
                                            Collection type
                                        </label>
                                        <select
                                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                            value={originKind}
                                            onChange={(e) => {
                                                const v = e.target
                                                    .value as typeof originKind;
                                                setOriginKind(v);
                                                if (
                                                    v === '' ||
                                                    v ===
                                                        MusicTrackOriginKind.Album
                                                ) {
                                                    setOriginTitle('');
                                                }
                                            }}
                                        >
                                            <option value="">
                                                Not specified (album or single)
                                            </option>
                                            {ORIGIN_OPTIONS.map((o) => (
                                                <option
                                                    key={o.value}
                                                    value={o.value}
                                                >
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    {originKind === '' ||
                                    originKind ===
                                        MusicTrackOriginKind.Album ? (
                                        <div className="sm:col-span-2">
                                            <label className="text-muted mb-1 block text-sm font-medium">
                                                Album (optional)
                                            </label>
                                            <input
                                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                                value={albumName}
                                                onChange={(e) =>
                                                    setAlbumName(e.target.value)
                                                }
                                                placeholder="e.g. Abbey Road — also used to group your library"
                                                autoComplete="off"
                                            />
                                            <p className="text-muted mt-1 text-xs">
                                                When set, your library groups
                                                this track under this album name.
                                                Leave empty for singles and
                                                one-offs.
                                            </p>
                                        </div>
                                    ) : (
                                        <>
                                            <div className="sm:col-span-2">
                                                <label className="text-muted mb-1 block text-sm font-medium">
                                                    Game, film, or series title
                                                </label>
                                                <input
                                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                                    value={originTitle}
                                                    onChange={(e) =>
                                                        setOriginTitle(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="e.g. Cyberpunk 2077, Dune, Stranger Things"
                                                    autoComplete="off"
                                                />
                                                <p className="text-muted mt-1 text-xs">
                                                    This is the group heading in
                                                    your library for this
                                                    collection type.
                                                </p>
                                            </div>
                                            <div className="sm:col-span-2">
                                                <label className="text-muted mb-1 block text-sm font-medium">
                                                    Soundtrack or release album
                                                    (optional)
                                                </label>
                                                <input
                                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                                    value={albumName}
                                                    onChange={(e) =>
                                                        setAlbumName(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="e.g. Original Score, Season 1 soundtrack"
                                                    autoComplete="off"
                                                />
                                                <p className="text-muted mt-1 text-xs">
                                                    Use when the disc or
                                                    soundtrack title is different
                                                    from the work above.
                                                </p>
                                            </div>
                                        </>
                                    )}
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
                                            onChange={(e) =>
                                                setReleaseYear(e.target.value)
                                            }
                                        />
                                    </div>
                                    <div>
                                        <label className="text-muted mb-1 block text-sm font-medium">
                                            Genre label (optional)
                                        </label>
                                        <input
                                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                            value={genre}
                                            onChange={(e) =>
                                                setGenre(e.target.value)
                                            }
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
                                            onChange={(e) =>
                                                setDurationSeconds(
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>
                    <Button type="button" onClick={() => void handleCreate()}>
                        {addMode === 'upload' ? 'Upload track' : 'Add track'}
                    </Button>
                </div>
            </details>

            <ConfirmModal
                isOpen={deleteTargetId !== null}
                onClose={() => setDeleteTargetId(null)}
                onConfirm={() => void handleConfirmDelete()}
                title="Delete track"
                message="This removes the track from your library. Quiz questions that still reference it may need to be updated separately."
                confirmText="Delete"
                cancelText="Cancel"
            />
        </div>
    );
}

interface TrackRowProps {
    readonly track: MusicTrackData;
    readonly sub_categories: readonly IdLabelOptionData[];
    readonly music_sources: readonly IdLabelOptionData[];
    readonly onSaved: () => void;
    readonly onRequestDelete: () => void;
}

function TrackRow({
    track,
    sub_categories,
    music_sources,
    onSaved,
    onRequestDelete,
}: TrackRowProps) {
    const [title, setTitle] = useState(track.title);
    const [artist, setArtist] = useState(track.artist_name);
    const [albumName, setAlbumName] = useState(track.album_name ?? '');
    const [releaseYear, setReleaseYear] = useState(
        track.release_year != null ? String(track.release_year) : '',
    );
    const [genre, setGenre] = useState(track.genre ?? '');
    const [durationSeconds, setDurationSeconds] = useState(
        track.duration_ms != null
            ? String(track.duration_ms / 1000)
            : '',
    );
    const [subCategoryId, setSubCategoryId] = useState(track.sub_category_id);
    const [sourceId, setSourceId] = useState(track.primary_source_id);
    const [originKind, setOriginKind] = useState<
        '' | MusicTrackOriginKindValue
    >(track.origin_kind ?? '');
    const [originTitle, setOriginTitle] = useState(track.origin_title ?? '');
    const [saving, setSaving] = useState(false);

    const isUploadBacked = Boolean(track.user_upload_path);
    const originLine = originSummary(track);
    const groupName = groupHeading(track);
    const albumTrim = track.album_name?.trim();
    const showAlbumMeta = Boolean(albumTrim && albumTrim !== groupName);

    const handleSave = async () => {
        if (!title.trim() || !artist.trim()) {
            toast.error('Title and artist are required');
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

        if (!isUploadBacked && !sourceId) {
            toast.error('Choose a streaming catalog');
            return;
        }

        const albumish =
            originKind === '' || originKind === MusicTrackOriginKind.Album;

        setSaving(true);
        const result = await updateMusicTrack(track.id, {
            title: title.trim(),
            artist_name: artist.trim(),
            album_name: albumName.trim() || null,
            release_year: yearParsed,
            genre: genre.trim() || null,
            duration_ms: durationMs,
            sub_category_id: subCategoryId,
            primary_source_id: isUploadBacked ? undefined : sourceId,
            origin_kind: originKind === '' ? null : originKind,
            origin_title: albumish ? null : originTitle.trim() || null,
        });
        setSaving(false);

        if (result._tag === 'Success') {
            toast.success('Track updated');
            onSaved();
        } else {
            toast.error('Could not update track');
        }
    };

    return (
        <details className="bg-background/60 rounded-lg border border-transparent dark:border-white/10">
            <summary
                className={cn(
                    'cursor-pointer list-none px-3 py-2 marker:hidden [&::-webkit-details-marker]:hidden',
                )}
            >
                <div className="flex flex-col gap-3">
                    <div className="flex flex-wrap items-start justify-between gap-2">
                        <div className="min-w-0 flex-1">
                            <div className="font-medium">
                                {track.title} — {track.artist_name}
                            </div>
                            <div className="text-muted mt-0.5 flex flex-wrap gap-x-2 gap-y-1 text-xs">
                                {originLine ? <span>{originLine}</span> : null}
                                {showAlbumMeta ? (
                                    <span>Album: {track.album_name}</span>
                                ) : null}
                                {track.primary_source?.display_name ? (
                                    <span>{track.primary_source.display_name}</span>
                                ) : null}
                                {isUploadBacked ? (
                                    <span className="rounded bg-secondary px-1.5 py-0.5">
                                        File upload
                                    </span>
                                ) : null}
                            </div>
                        </div>
                        <span className="text-muted shrink-0 text-xs">
                            Tap to edit
                        </span>
                    </div>
                    <div
                        className="border-t border-transparent pt-2 dark:border-white/10"
                        onClick={(event) => event.stopPropagation()}
                        onKeyDown={(event) => event.stopPropagation()}
                        role="presentation"
                    >
                        <TrackUploadAudioPlayer
                            trackId={track.id}
                            trackTitle={`${track.title} — ${track.artist_name}`}
                            hasUpload={isUploadBacked}
                        />
                    </div>
                </div>
            </summary>
            <div className="flex flex-col gap-3 border-t border-transparent px-3 pb-3 pt-2 dark:border-white/10">
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Title
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Artist
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={artist}
                            onChange={(e) => setArtist(e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Style / sub-genre
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={subCategoryId}
                            onChange={(e) => setSubCategoryId(e.target.value)}
                        >
                            {sub_categories.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    {!isUploadBacked ? (
                        <div className="sm:col-span-2">
                            <label className="text-muted mb-1 block text-xs font-medium">
                                Primary streaming catalog
                            </label>
                            <select
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={sourceId}
                                onChange={(e) => setSourceId(e.target.value)}
                            >
                                {music_sources.map((opt) => (
                                    <option key={opt.id} value={opt.id}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    ) : (
                        <div className="text-muted sm:col-span-2 text-xs">
                            This row is tied to your uploaded file (
                            {track.user_upload_original_name ?? 'audio'}
                            ). The catalog source stays on &ldquo;My audio file
                            (upload)&rdquo;; delete the track to remove the file.
                        </div>
                    )}
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Collection type
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={originKind}
                            onChange={(e) => {
                                const v = e.target.value as typeof originKind;
                                setOriginKind(v);
                                if (
                                    v === '' ||
                                    v === MusicTrackOriginKind.Album
                                ) {
                                    setOriginTitle('');
                                }
                            }}
                        >
                            <option value="">
                                Not specified (album or single)
                            </option>
                            {ORIGIN_OPTIONS.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    {originKind === '' ||
                    originKind === MusicTrackOriginKind.Album ? (
                        <div className="sm:col-span-2">
                            <label className="text-muted mb-1 block text-xs font-medium">
                                Album (optional)
                            </label>
                            <input
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={albumName}
                                onChange={(e) => setAlbumName(e.target.value)}
                                placeholder="Groups your library when set"
                            />
                        </div>
                    ) : (
                        <>
                            <div className="sm:col-span-2">
                                <label className="text-muted mb-1 block text-xs font-medium">
                                    Game, film, or series title
                                </label>
                                <input
                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                    value={originTitle}
                                    onChange={(e) =>
                                        setOriginTitle(e.target.value)
                                    }
                                />
                            </div>
                            <div className="sm:col-span-2">
                                <label className="text-muted mb-1 block text-xs font-medium">
                                    Soundtrack or release album (optional)
                                </label>
                                <input
                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                    value={albumName}
                                    onChange={(e) => setAlbumName(e.target.value)}
                                />
                            </div>
                        </>
                    )}
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Release year
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
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Genre
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={genre}
                            onChange={(e) => setGenre(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Duration (seconds)
                        </label>
                        <input
                            type="number"
                            min={0}
                            step="0.1"
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={durationSeconds}
                            onChange={(e) =>
                                setDurationSeconds(e.target.value)
                            }
                        />
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        disabled={saving}
                        onClick={() => void handleSave()}
                    >
                        Save changes
                    </Button>
                    <Button
                        type="button"
                        variant="danger"
                        onClick={onRequestDelete}
                    >
                        Delete
                    </Button>
                </div>
            </div>
        </details>
    );
}
