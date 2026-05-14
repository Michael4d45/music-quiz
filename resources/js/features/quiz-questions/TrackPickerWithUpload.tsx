import { Button } from '@/components/ui/Button';
import { uploadMusicTrack } from '@/features/music-tracks/api';
import {
    groupHeading,
    sortGroupEntries,
} from '@/features/music-tracks/trackGrouping';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import { useMemo, useState } from 'react';
import toast from 'react-hot-toast';

export interface TrackPickerWithUploadProps {
    readonly tracks: readonly MusicTrackData[];
    readonly subCategories: readonly IdLabelOptionData[];
    readonly selectedTrackId: string;
    readonly onSelectedTrackIdChange: (id: string) => void;
    readonly onTrackCreated?: (track: MusicTrackData) => void;
    readonly disabled?: boolean;
    readonly selectLabel?: string;
    readonly noneOptionLabel?: string;
    readonly emptyTracksHint?: string | null;
}

function trackSelectOptgroups(tracks: readonly MusicTrackData[]) {
    const map = new Map<string, MusicTrackData[]>();
    for (const t of tracks) {
        const heading = groupHeading(t);
        const list = map.get(heading) ?? [];
        list.push(t);
        map.set(heading, list);
    }
    for (const list of map.values()) {
        list.sort((a, b) =>
            `${a.artist_name} ${a.title}`.localeCompare(
                `${b.artist_name} ${b.title}`,
            ),
        );
    }
    return sortGroupEntries([...map.entries()]);
}

export function TrackPickerWithUpload({
    tracks,
    subCategories,
    selectedTrackId,
    onSelectedTrackIdChange,
    onTrackCreated,
    disabled = false,
    selectLabel = 'Linked track (optional)',
    noneOptionLabel = 'None — standalone question',
    emptyTracksHint = 'Add tracks under My tracks in the sidebar, or upload a clip below.',
}: TrackPickerWithUploadProps) {
    const [uploadTitle, setUploadTitle] = useState('');
    const [uploadArtist, setUploadArtist] = useState('');
    const [uploadSubCategoryId, setUploadSubCategoryId] = useState('');
    const [uploadFile, setUploadFile] = useState<File | null>(null);
    const [uploading, setUploading] = useState(false);

    const trackOptgroups = trackSelectOptgroups(tracks);

    const handleUpload = async () => {
        if (!uploadTitle.trim() || !uploadArtist.trim()) {
            toast.error('Title and artist are required for an upload');
            return;
        }
        if (!uploadSubCategoryId) {
            toast.error('Choose a style category for the clip');
            return;
        }
        if (!uploadFile) {
            toast.error('Choose an audio file');
            return;
        }
        setUploading(true);
        const result = await uploadMusicTrack(
            {
                title: uploadTitle.trim(),
                artist_name: uploadArtist.trim(),
                sub_category_id: uploadSubCategoryId,
            },
            uploadFile,
        );
        setUploading(false);
        if (result._tag === 'Success') {
            toast.success('Track uploaded and selected');
            onTrackCreated?.(result.data);
            onSelectedTrackIdChange(result.data.id);
            setUploadTitle('');
            setUploadArtist('');
            setUploadSubCategoryId('');
            setUploadFile(null);
        } else {
            toast.error('Could not upload track');
        }
    };

    return (
        <div className="flex flex-col gap-3">
            <div>
                <label className="text-muted mb-1 block text-sm font-medium">
                    {selectLabel}
                </label>
                <select
                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                    value={selectedTrackId}
                    disabled={disabled}
                    onChange={(e) => onSelectedTrackIdChange(e.target.value)}
                >
                    <option value="">{noneOptionLabel}</option>
                    {trackOptgroups.map(([groupLabel, trackList]) => (
                        <optgroup key={groupLabel} label={groupLabel}>
                            {trackList.map((t) => (
                                <option key={t.id} value={t.id}>
                                    {t.title} — {t.artist_name}
                                </option>
                            ))}
                        </optgroup>
                    ))}
                </select>
                {tracks.length === 0 && emptyTracksHint ? (
                    <p className="text-muted mt-1 text-xs">{emptyTracksHint}</p>
                ) : null}
            </div>

            <details className="rounded-md border border-dashed border-transparent p-1 dark:border-white/15">
                <summary className="text-muted cursor-pointer text-sm font-medium">
                    Upload a new audio clip
                </summary>
                <div className="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Title
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={uploadTitle}
                            disabled={disabled || uploading}
                            onChange={(e) => setUploadTitle(e.target.value)}
                            autoComplete="off"
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Artist
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={uploadArtist}
                            disabled={disabled || uploading}
                            onChange={(e) => setUploadArtist(e.target.value)}
                            autoComplete="off"
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Style category
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={uploadSubCategoryId}
                            disabled={disabled || uploading}
                            onChange={(e) => setUploadSubCategoryId(e.target.value)}
                        >
                            <option value="">Select…</option>
                            {subCategories.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Audio file
                        </label>
                        <input
                            type="file"
                            accept="audio/*,.mp3,.wav,.m4a,.flac,.ogg"
                            disabled={disabled || uploading}
                            className="text-muted w-full text-sm file:mr-3 file:rounded file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium"
                            onChange={(e) => {
                                const f = e.target.files?.[0];
                                setUploadFile(f ?? null);
                            }}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <Button
                            type="button"
                            variant="secondary"
                            disabled={disabled || uploading}
                            onClick={() => void handleUpload()}
                        >
                            {uploading ? 'Uploading…' : 'Upload and use this clip'}
                        </Button>
                    </div>
                </div>
            </details>
        </div>
    );
}
