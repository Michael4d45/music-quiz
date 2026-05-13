import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import {
    addPlaylistItem,
    fetchPlaylistItems,
    removePlaylistItem,
} from '@/features/playlists/api';
import type { MyPlaylistItemsResponseData } from '@/schemas/App/Data/Responses/MyPlaylistItemsResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import {
    useLoaderData,
    useRevalidator,
} from 'react-router-dom';

export async function playlistDetailLoader({
    params,
}: {
    params: { playlistId?: string };
}): Promise<MyPlaylistItemsResponseData & { playlistId: string }> {
    const playlistId = params.playlistId;
    if (!playlistId) {
        return { items: [], playlistId: '' };
    }
    const result = await fetchPlaylistItems(playlistId);
    if (result._tag === 'Success') {
        return { ...result.data, playlistId };
    }
    return { items: [], playlistId };
}

export function PlaylistDetailPage() {
    const { items, playlistId } = useLoaderData<
        MyPlaylistItemsResponseData & { playlistId: string }
    >();
    const id = playlistId;
    const revalidator = useRevalidator();
    const [questionId, setQuestionId] = useState('');

    const handleAdd = async () => {
        if (!questionId.trim()) {
            toast.error('Enter a question ID');
            return;
        }
        const result = await addPlaylistItem(id, questionId.trim());
        if (result._tag === 'Success') {
            toast.success('Question added');
            setQuestionId('');
            revalidator.revalidate();
        } else {
            toast.error('Could not add question');
        }
    };

    const handleRemove = async (itemId: string) => {
        const result = await removePlaylistItem(id, itemId);
        if (result._tag === 'Success') {
            toast.success('Removed');
            revalidator.revalidate();
        } else {
            toast.error('Could not remove');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6">
                <ButtonLink to="/my/playlists" variant="secondary">
                    Back to playlists
                </ButtonLink>
            </div>
            <h1 className="mb-6 text-2xl font-bold">Playlist items</h1>

            <div className="bg-card mb-8 flex flex-col gap-3 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10 sm:flex-row sm:items-end">
                <div className="grow">
                    <label
                        htmlFor="question-id"
                        className="text-muted mb-1 block text-sm font-medium"
                    >
                        Question ID (UUID)
                    </label>
                    <input
                        id="question-id"
                        className="border-input bg-background w-full rounded-md border px-3 py-2 font-mono text-sm"
                        value={questionId}
                        onChange={(e) => setQuestionId(e.target.value)}
                    />
                </div>
                <Button type="button" onClick={() => void handleAdd()}>
                    Add to playlist
                </Button>
            </div>

            {items.length === 0 ? (
                <p className="text-muted">No items in this playlist.</p>
            ) : (
                <ul className="flex flex-col gap-2" role="list">
                    {items.map((item) => (
                        <li
                            key={item.id}
                            className="bg-card flex items-center justify-between gap-3 rounded-lg border border-transparent px-4 py-3 shadow-md dark:border-white/10"
                        >
                            <div>
                                <span className="font-mono text-sm">
                                    {item.question_id}
                                </span>
                                <span className="text-muted ml-2 text-sm">
                                    order {item.sort_order}
                                </span>
                            </div>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => void handleRemove(item.id)}
                            >
                                Remove
                            </Button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
