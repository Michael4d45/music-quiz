import ConfirmModal from '@/components/ConfirmModal';
import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import {
    addPlaylistItem,
    fetchPlaylistItems,
    removePlaylistItem,
    updatePlaylistItemPosition,
} from '@/features/playlists/api';
import { fetchMyQuizQuestions } from '@/features/quiz-questions/api';
import { fetchQuestionTypes } from '@/features/reference/api';
import { cn } from '@/lib/utils';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { PlaylistItemData } from '@/schemas/App/Data/Models/PlaylistItemData';
import type { QuizQuestionData } from '@/schemas/App/Data/Models/QuizQuestionData';
import type { MyPlaylistItemsResponseData } from '@/schemas/App/Data/Responses/MyPlaylistItemsResponseData';
import type { QuestionType } from '@/schemas/App/Enums/QuestionType';
import { useEffect, useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { data, type LoaderFunctionArgs } from 'react-router';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export interface PlaylistDetailLoaderData extends MyPlaylistItemsResponseData {
    readonly playlistId: string;
    readonly questions: readonly QuizQuestionData[];
    readonly question_types: readonly IdLabelOptionData[];
}

export async function playlistDetailLoader({ params }: LoaderFunctionArgs) {
    const playlistId = params.playlistId?.trim();
    if (!playlistId) {
        return data(null, { status: 404 });
    }
    const [itemsRes, questionsRes, typesRes] = await Promise.all([
        fetchPlaylistItems(playlistId),
        fetchMyQuizQuestions(),
        fetchQuestionTypes(),
    ]);
    if (itemsRes._tag !== 'Success') {
        return data(null, { status: 500 });
    }
    return {
        ...itemsRes.data,
        playlistId,
        questions:
            questionsRes._tag === 'Success' ? questionsRes.data.questions : [],
        question_types:
            typesRes._tag === 'Success' ? typesRes.data.question_types : [],
    };
}

function questionTypeLabel(
    types: readonly IdLabelOptionData[],
    value: QuestionType,
): string {
    const found = types.find((t) => t.id === value);
    return found?.label ?? value;
}

function itemSearchBlob(item: PlaylistItemData): string {
    const q = item.question;
    return [
        q?.prompt_text ?? '',
        q?.correct_answer ?? '',
        q?.question_type ?? '',
    ]
        .join(' ')
        .toLowerCase();
}

export function PlaylistDetailPage() {
    const loaderData = useLoaderData<PlaylistDetailLoaderData>();
    const {
        playlistId,
        questions,
        question_types: questionTypes,
    } = loaderData;
    const revalidator = useRevalidator();
    const itemsFingerprint = loaderData.items
                .map((i) => `${i.id}:${i.sort_order}`)
                .join('|');
    const [localPayload, setLocalPayload] =
        useState<MyPlaylistItemsResponseData | null>(null);

    useEffect(() => {
        // Drop merged list when the route loader supplies a new item ordering (revalidate / navigate).
        // eslint-disable-next-line react-hooks/set-state-in-effect -- intentional sync from loader fingerprint
        setLocalPayload(null);
    }, [itemsFingerprint]);

    const playlist = localPayload?.playlist ?? loaderData.playlist;
    const items = localPayload?.items ?? loaderData.items;

    const [search, setSearch] = useState('');
    const [selectedQuestionId, setSelectedQuestionId] = useState('');
    const [pendingRemoveItemId, setPendingRemoveItemId] = useState<
        string | null
    >(null);
    const [reordering, setReordering] = useState(false);

    const usedQuestionIds = new Set(items.map((i) => i.question_id));

    const addableQuestions = questions.filter((q) => !usedQuestionIds.has(q.id));

    const filteredItems = (() => {
        const q = search.trim().toLowerCase();
        if (!q) {
            return items;
        }
        return items.filter((item) => itemSearchBlob(item).includes(q));
    })();

    const handleAdd = async () => {
        if (!selectedQuestionId) {
            toast.error('Choose a question');
            return;
        }
        const result = await addPlaylistItem(playlistId, selectedQuestionId);
        if (result._tag === 'Success') {
            toast.success('Question added to playlist');
            setSelectedQuestionId('');
            revalidator.revalidate();
        } else {
            toast.error('Could not add question');
        }
    };

    const handleConfirmRemove = async (itemId: string) => {
        const result = await removePlaylistItem(playlistId, itemId);
        if (result._tag === 'Success') {
            toast.success('Removed from playlist');
            setPendingRemoveItemId(null);
            revalidator.revalidate();
        } else {
            toast.error('Could not remove');
        }
    };

    const moveItem = async (
        playlistItemId: string,
        beforeItemId: string | null,
    ) => {
        setReordering(true);
        const result = await updatePlaylistItemPosition(
            playlistId,
            playlistItemId,
            { before_item_id: beforeItemId },
        );
        setReordering(false);
        if (result._tag === 'Success') {
            toast.success('Order updated');
            setLocalPayload(result.data);
        } else {
            toast.error('Could not reorder');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <h1 className="mb-2 text-2xl font-bold">{playlist.name}</h1>
            {playlist.description?.trim() ? (
                <p className="text-muted mb-4 max-w-2xl text-sm">
                    {playlist.description}
                </p>
            ) : null}

            <div className="text-muted mb-6 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                <span className="rounded bg-secondary/80 px-1.5 py-0.5">
                    {playlist.status}
                </span>
                <span>{playlist.visibility}</span>
                <span>Order: {playlist.question_order}</span>
                <span>Played {playlist.play_count}×</span>
            </div>

            <PageIntroExpandable
                summary="Use Up and Down to set play order (one save per tap). Add questions from your library below."
                moreLabel="How this playlist is used in a game"
            >
                <p className="text-muted text-sm">
                    Removing a question only takes it out of this playlist; it
                    stays in My quiz questions. When you host with this playlist
                    attached, rounds follow the order shown here (subject to the
                    playlist's order mode in the editor).
                </p>
            </PageIntroExpandable>

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label className="flex min-w-0 flex-1 flex-col gap-1 text-sm">
                    <span className="text-muted font-medium">Search items</span>
                    <input
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Prompt, answer, or type…"
                        autoComplete="off"
                    />
                </label>
            </div>

            <div className="mb-8 flex flex-col gap-3">
                <h2 className="text-lg font-semibold">Questions in this playlist</h2>
                {items.length === 0 ? (
                    <p className="text-muted">
                        No questions yet. Add one from your library below.
                    </p>
                ) : filteredItems.length === 0 ? (
                    <p className="text-muted">No items match your search.</p>
                ) : (
                    <ul className="flex flex-col gap-2" role="list">
                        {filteredItems.map((item) => {
                            const globalIndex = items.findIndex(
                                (i) => i.id === item.id,
                            );
                            const q = item.question;
                            const typeLabel = q
                                ? questionTypeLabel(questionTypes, q.question_type)
                                : null;
                            return (
                                <li key={item.id} role="listitem">
                                    <div
                                        className={cn(
                                            'bg-card flex flex-col gap-3 rounded-lg border border-transparent px-4 py-3 shadow-md sm:flex-row sm:items-stretch sm:justify-between dark:border-white/10',
                                        )}
                                    >
                                        <div className="min-w-0 flex-1 space-y-1">
                                            <div className="font-medium">
                                                {q?.prompt_text?.trim()
                                                    ? q.prompt_text
                                                    : 'Untitled question'}
                                            </div>
                                            <div className="text-muted flex flex-wrap gap-x-2 gap-y-1 text-xs">
                                                {typeLabel ? (
                                                    <span className="rounded bg-secondary/80 px-1.5 py-0.5">
                                                        {typeLabel}
                                                    </span>
                                                ) : null}
                                            </div>
                                            {q?.correct_answer ? (
                                                <p className="text-muted line-clamp-2 text-sm">
                                                    Answer: {q.correct_answer}
                                                </p>
                                            ) : null}
                                        </div>
                                        <div className="flex shrink-0 flex-col gap-2 sm:w-40">
                                            <div className="flex gap-2">
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                    className="min-w-0 flex-1 text-xs"
                                                    disabled={
                                                        reordering ||
                                                        globalIndex <= 0
                                                    }
                                                    onClick={() => {
                                                        const beforeId =
                                                            items[
                                                                globalIndex - 1
                                                            ]?.id;
                                                        if (!beforeId) {
                                                            return;
                                                        }
                                                        void moveItem(
                                                            item.id,
                                                            beforeId,
                                                        );
                                                    }}
                                                >
                                                    Up
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="secondary"
                                                    className="min-w-0 flex-1 text-xs"
                                                    disabled={
                                                        reordering ||
                                                        globalIndex < 0 ||
                                                        globalIndex >=
                                                            items.length - 1
                                                    }
                                                    onClick={() => {
                                                        const beforeId =
                                                            globalIndex + 2 <
                                                            items.length
                                                                ? items[
                                                                      globalIndex +
                                                                          2
                                                                  ].id
                                                                : null;
                                                        void moveItem(
                                                            item.id,
                                                            beforeId,
                                                        );
                                                    }}
                                                >
                                                    Down
                                                </Button>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="danger"
                                                disabled={reordering}
                                                onClick={() =>
                                                    setPendingRemoveItemId(
                                                        item.id,
                                                    )
                                                }
                                            >
                                                Remove
                                            </Button>
                                        </div>
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </div>

            <details className="bg-card mb-8 rounded-lg border border-transparent shadow-md dark:border-white/10">
                <summary className="cursor-pointer list-none px-4 py-3 text-lg font-semibold marker:hidden [&::-webkit-details-marker]:hidden">
                    Add questions
                </summary>
                <div className="flex flex-col gap-4 border-t border-transparent px-4 pb-4 pt-3 dark:border-white/10">
                    {addableQuestions.length === 0 ? (
                        <p className="text-muted text-sm">
                            {questions.length === 0
                                ? 'You have no quiz questions yet. Create some under My quiz questions.'
                                : 'Every question you own is already in this playlist (or none are left to add).'}
                        </p>
                    ) : (
                        <>
                            <label className="flex flex-col gap-1 text-sm">
                                <span className="text-muted font-medium">
                                    Question
                                </span>
                                <select
                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                    value={selectedQuestionId}
                                    onChange={(e) =>
                                        setSelectedQuestionId(e.target.value)
                                    }
                                >
                                    <option value="">
                                        Select a question…
                                    </option>
                                    {addableQuestions.map((q) => (
                                        <option key={q.id} value={q.id}>
                                            {(q.prompt_text?.trim()
                                                ? q.prompt_text.slice(0, 72)
                                                : 'Untitled') +
                                                ` — ${q.id.slice(0, 8)}…`}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <Button
                                type="button"
                                onClick={() => void handleAdd()}
                                disabled={!selectedQuestionId}
                            >
                                Add to playlist
                            </Button>
                        </>
                    )}
                </div>
            </details>

            <ConfirmModal
                isOpen={pendingRemoveItemId !== null}
                onClose={() => setPendingRemoveItemId(null)}
                onConfirm={() => {
                    if (pendingRemoveItemId !== null) {
                        void handleConfirmRemove(pendingRemoveItemId);
                    }
                }}
                title="Remove from playlist?"
                message="The quiz question is not deleted; it is only removed from this playlist."
                confirmText="Remove"
                cancelText="Cancel"
            />
        </div>
    );
}
