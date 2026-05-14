import ConfirmModal from '@/components/ConfirmModal';
import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import {
    createPlaylist,
    deletePlaylist,
    updatePlaylist,
} from '@/features/playlists/api';
import { cn } from '@/lib/utils';
import type { PlaylistData } from '@/schemas/App/Data/Models/PlaylistData';
import type { MyPlaylistsResponseData } from '@/schemas/App/Data/Responses/MyPlaylistsResponseData';
import {
    PlaylistStatus,
    type PlaylistStatus as PlaylistStatusValue,
} from '@/schemas/App/Enums/PlaylistStatus';
import { Visibility } from '@/schemas/App/Enums/Visibility';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useRevalidator } from 'react-router-dom';

const VISIBILITY_OPTIONS: {
    value: (typeof Visibility)[keyof typeof Visibility];
    label: string;
}[] = [
    { value: Visibility.Private, label: 'Private' },
    {
        value: Visibility.Draft,
        label: 'Link-only',
    },
    { value: Visibility.Public, label: 'Public' },
];

const STATUS_OPTIONS: {
    value: (typeof PlaylistStatus)[keyof typeof PlaylistStatus];
    label: string;
}[] = [
    { value: PlaylistStatus.Draft, label: 'Draft' },
    { value: PlaylistStatus.Published, label: 'Published' },
    { value: PlaylistStatus.Archived, label: 'Archived' },
];

const STATUS_GROUP_ORDER: readonly PlaylistStatusValue[] = [
    PlaylistStatus.Published,
    PlaylistStatus.Draft,
    PlaylistStatus.Archived,
];

const STATUS_GROUP_HEADING: Record<PlaylistStatusValue, string> = {
    [PlaylistStatus.Published]: 'Published playlists',
    [PlaylistStatus.Draft]: 'Draft playlists',
    [PlaylistStatus.Archived]: 'Archived playlists',
};

function playlistGroupHeading(status: PlaylistStatusValue): string {
    return STATUS_GROUP_HEADING[status];
}

function sortPlaylistGroupsByStatus(
    entries: [PlaylistStatusValue, PlaylistData[]][],
): [PlaylistStatusValue, PlaylistData[]][] {
    return [...entries].sort(([a], [b]) => {
        return (
            STATUS_GROUP_ORDER.indexOf(a) - STATUS_GROUP_ORDER.indexOf(b)
        );
    });
}

export function MyPlaylistsPage() {
    const { playlists } = useLoaderData<MyPlaylistsResponseData>();
    const revalidator = useRevalidator();
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [search, setSearch] = useState('');
    const [pendingDeleteId, setPendingDeleteId] = useState<string | null>(null);

    const filteredPlaylists = (() => {
        const q = search.trim().toLowerCase();
        if (!q) {
            return playlists;
        }
        return playlists.filter((p) => {
            const blob = [
                p.name,
                p.description ?? '',
                p.status,
                p.visibility,
                String(p.play_count),
            ]
                .join(' ')
                .toLowerCase();
            return blob.includes(q);
        });
    })();

    const groupedPlaylists = (() => {
        const map = new Map<PlaylistStatusValue, PlaylistData[]>();
        for (const p of filteredPlaylists) {
            const list = map.get(p.status) ?? [];
            list.push(p);
            map.set(p.status, list);
        }
        for (const list of map.values()) {
            list.sort(
                (a, b) =>
                    (b.updated_at?.getTime() ?? 0) -
                    (a.updated_at?.getTime() ?? 0),
            );
        }
        return sortPlaylistGroupsByStatus([...map.entries()]).map(
            ([status, list]) => ({
                key: status,
                heading: playlistGroupHeading(status),
                list,
            }),
        );
    })();

    const handleCreate = async () => {
        if (!name.trim()) {
            toast.error('Enter a name');
            return;
        }
        const result = await createPlaylist({
            name: name.trim(),
            description: description.trim() || null,
        });
        if (result._tag === 'Success') {
            toast.success('Playlist created');
            setName('');
            setDescription('');
            revalidator.revalidate();
        } else {
            toast.error('Could not create playlist');
        }
    };

    const handleConfirmDelete = async (id: string) => {
        const result = await deletePlaylist(id);
        if (result._tag === 'Success') {
            toast.success('Playlist deleted');
            revalidator.revalidate();
        } else {
            toast.error('Could not delete playlist');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">My playlists</h1>
                <div className="flex flex-wrap gap-2">
                    <ButtonLink to="/my/music-tracks" variant="secondary">
                        My tracks
                    </ButtonLink>
                    <ButtonLink to="/my/quiz-questions" variant="secondary">
                        My quiz questions
                    </ButtonLink>
                </div>
            </div>

            <PageIntroExpandable
                summary="Ordered lists of quiz questions you can attach when hosting. Open a playlist to reorder items; edit name, visibility, and status here."
                moreLabel="How playlists work in hosting"
            >
                <p>
                    A playlist is an ordered set of quiz questions. Open one to
                    add or reorder items, then pick it when you host so rounds
                    draw from that set. Edit name, visibility, or status here;
                    fine-tune question order on the playlist detail page.
                </p>
            </PageIntroExpandable>

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label className="flex min-w-0 flex-1 flex-col gap-1 text-sm">
                    <span className="text-muted font-medium">Search</span>
                    <input
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Name, description, status, or visibility…"
                        autoComplete="off"
                    />
                </label>
            </div>

            <div className="mb-8 flex flex-col gap-4">
                <h2 className="text-lg font-semibold">Playlist library</h2>
                {groupedPlaylists.length === 0 ? (
                    <p className="text-muted">
                        {playlists.length === 0
                            ? 'No playlists yet. Create your first one below.'
                            : 'No playlists match your search.'}
                    </p>
                ) : (
                    <div className="flex flex-col gap-3">
                        {groupedPlaylists.map((group) => (
                            <details
                                key={group.key}
                                className="bg-card rounded-lg border border-transparent shadow-md open:border-transparent dark:border-white/10"
                            >
                                <summary className="cursor-pointer list-none rounded-lg px-4 py-3 marker:hidden [&::-webkit-details-marker]:hidden">
                                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                                        <span className="font-semibold">
                                            {group.heading}
                                        </span>
                                        <span className="text-muted text-sm">
                                            {group.list.length}{' '}
                                            {group.list.length === 1
                                                ? 'playlist'
                                                : 'playlists'}
                                        </span>
                                    </div>
                                </summary>
                                <ul
                                    className="flex flex-col gap-2 border-t border-transparent px-2 pb-3 pt-1 dark:border-white/10"
                                    role="list"
                                >
                                    {group.list.map((p) => (
                                        <li key={p.id}>
                                            <PlaylistRow
                                                key={`${p.id}-${p.updated_at?.toString() ?? ''}`}
                                                playlist={p}
                                                onSaved={() =>
                                                    revalidator.revalidate()
                                                }
                                                onRequestDelete={() =>
                                                    setPendingDeleteId(p.id)
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
                    Create a playlist
                </summary>
                <div className="flex flex-col gap-4 border-t border-transparent px-4 pb-4 pt-3 dark:border-white/10">
                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="sm:col-span-2">
                            <label
                                htmlFor="new-playlist-name"
                                className="text-muted mb-1 block text-sm font-medium"
                            >
                                Name
                            </label>
                            <input
                                id="new-playlist-name"
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                autoComplete="off"
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <label
                                htmlFor="new-playlist-description"
                                className="text-muted mb-1 block text-sm font-medium"
                            >
                                Description (optional)
                            </label>
                            <textarea
                                id="new-playlist-description"
                                rows={2}
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                placeholder="Shown on the detail page and when picking a list to host."
                            />
                        </div>
                    </div>
                    <Button type="button" onClick={() => void handleCreate()}>
                        Create playlist
                    </Button>
                </div>
            </details>

            <ConfirmModal
                isOpen={pendingDeleteId !== null}
                onClose={() => setPendingDeleteId(null)}
                onConfirm={() => {
                    if (pendingDeleteId !== null) {
                        void handleConfirmDelete(pendingDeleteId);
                    }
                }}
                title="Delete playlist?"
                message="This removes the playlist and its item order. Quiz questions themselves are not deleted."
                confirmText="Delete"
                cancelText="Cancel"
            />
        </div>
    );
}

interface PlaylistRowProps {
    readonly playlist: PlaylistData;
    readonly onSaved: () => void;
    readonly onRequestDelete: () => void;
}

function PlaylistRow({ playlist: p, onSaved, onRequestDelete }: PlaylistRowProps) {
    const [name, setName] = useState(p.name);
    const [description, setDescription] = useState(p.description ?? '');
    const [status, setStatus] = useState(p.status);
    const [visibility, setVisibility] = useState(p.visibility);
    const [saving, setSaving] = useState(false);

    const handleSave = async () => {
        if (!name.trim()) {
            toast.error('Name is required');
            return;
        }
        setSaving(true);
        const result = await updatePlaylist(p.id, {
            name: name.trim(),
            description: description.trim() || null,
            status,
            visibility,
        });
        setSaving(false);
        if (result._tag === 'Success') {
            toast.success('Playlist updated');
            onSaved();
        } else {
            toast.error('Could not update playlist');
        }
    };

    return (
        <details className="bg-background/60 rounded-lg border border-transparent dark:border-white/10">
            <summary
                className={cn(
                    'cursor-pointer list-none px-3 py-2 marker:hidden [&::-webkit-details-marker]:hidden',
                )}
            >
                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div className="min-w-0 flex-1 space-y-1">
                        <div className="font-semibold">{name}</div>
                        <div className="text-muted flex flex-wrap gap-x-2 gap-y-1 text-xs">
                            <span className="rounded bg-secondary/80 px-1.5 py-0.5">
                                {status}
                            </span>
                            <span>{visibility}</span>
                            <span>Played {p.play_count}×</span>
                        </div>
                        {description.trim() ? (
                            <p className="text-muted line-clamp-2 text-sm">
                                {description}
                            </p>
                        ) : (
                            <p className="text-muted text-sm italic">
                                No description
                            </p>
                        )}
                    </div>
                    <div className="flex shrink-0 flex-col items-stretch gap-2 sm:items-end">
                        <ButtonLink
                            to={`/my/playlists/${p.id}`}
                            variant="secondary"
                            className="text-center text-sm"
                            onClick={(e) => e.stopPropagation()}
                        >
                            Open items
                        </ButtonLink>
                        <span className="text-muted text-center text-xs sm:text-right">
                            Tap to edit
                        </span>
                    </div>
                </div>
            </summary>
            <div className="flex flex-col gap-3 border-t border-transparent px-3 pb-3 pt-2 dark:border-white/10">
                <div className="grid gap-3 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Name
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Description
                        </label>
                        <textarea
                            rows={2}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Status
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={status}
                            onChange={(e) =>
                                setStatus(e.target.value as PlaylistStatusValue)
                            }
                        >
                            {STATUS_OPTIONS.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Visibility
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={visibility}
                            onChange={(e) =>
                                setVisibility(
                                    e.target
                                        .value as (typeof Visibility)[keyof typeof Visibility],
                                )
                            }
                        >
                            {VISIBILITY_OPTIONS.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </select>
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
