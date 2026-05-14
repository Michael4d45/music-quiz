import ConfirmModal from '@/components/ConfirmModal';
import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { PageHeader } from '@/components/layout/PageHeader';
import { PageShell } from '@/components/layout/PageShell';
import { Surface } from '@/components/layout/Surface';
import { Button } from '@/components/ui/Button';
import { fetchMyMusicTracks } from '@/features/music-tracks/api';
import {
    addPlaylistItem,
    createPlaylistQuestion,
    fetchPlaylistItems,
    removePlaylistItem,
    updatePlaylistItemPosition,
} from '@/features/playlists/api';
import { QuizQuestionTrackAudioPlayerFromQuestion } from '@/features/quiz-questions/QuizQuestionTrackAudioPlayer';
import { TrackPickerWithUpload } from '@/features/quiz-questions/TrackPickerWithUpload';
import { fetchMyQuizQuestions } from '@/features/quiz-questions/api';
import {
    fetchQuestionTypes,
    fetchSubCategories,
} from '@/features/reference/api';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import type { PlaylistItemData } from '@/schemas/App/Data/Models/PlaylistItemData';
import type { QuizQuestionData } from '@/schemas/App/Data/Models/QuizQuestionData';
import type { MyPlaylistItemsResponseData } from '@/schemas/App/Data/Responses/MyPlaylistItemsResponseData';
import type { QuestionType } from '@/schemas/App/Enums/QuestionType';
import { Visibility } from '@/schemas/App/Enums/Visibility';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { data, type LoaderFunctionArgs } from 'react-router';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export interface PlaylistDetailLoaderData extends MyPlaylistItemsResponseData {
    readonly playlistId: string;
    readonly questions: readonly QuizQuestionData[];
    readonly question_types: readonly IdLabelOptionData[];
    readonly tracks: readonly MusicTrackData[];
    readonly sub_categories: readonly IdLabelOptionData[];
}

export async function playlistDetailLoader({ params }: LoaderFunctionArgs) {
    const playlistId = params.playlistId?.trim();
    if (!playlistId) {
        return data(null, { status: 404 });
    }
    const [itemsRes, questionsRes, typesRes, tracksRes, subRes] =
        await Promise.all([
            fetchPlaylistItems(playlistId),
            fetchMyQuizQuestions(),
            fetchQuestionTypes(),
            fetchMyMusicTracks(),
            fetchSubCategories(),
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
        tracks: tracksRes._tag === 'Success' ? tracksRes.data.tracks : [],
        sub_categories:
            subRes._tag === 'Success' ? subRes.data.sub_categories : [],
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

function parseNullableNonNegativeInt(raw: string): number | null {
    const trimmed = raw.trim();
    if (trimmed === '') {
        return null;
    }
    const n = Number.parseInt(trimmed, 10);
    if (Number.isNaN(n) || n < 0) {
        return null;
    }
    return n;
}

export function PlaylistDetailPage() {
    const loaderData = useLoaderData<PlaylistDetailLoaderData>();
    const {
        playlistId,
        questions,
        question_types: questionTypes,
        tracks: loaderTracks,
        sub_categories,
    } = loaderData;
    const revalidator = useRevalidator();
    const itemsFingerprint = loaderData.items
        .map((i) => `${i.id}:${i.sort_order}`)
        .join('|');
    const [localItemsOverride, setLocalItemsOverride] = useState<{
        playlistId: string;
        loaderItemsFingerprint: string;
        payload: MyPlaylistItemsResponseData;
    } | null>(null);

    const mergedFromLocal =
        localItemsOverride &&
        localItemsOverride.playlistId === playlistId &&
        localItemsOverride.loaderItemsFingerprint === itemsFingerprint
            ? localItemsOverride.payload
            : null;

    const playlist = mergedFromLocal?.playlist ?? loaderData.playlist;
    const items = mergedFromLocal?.items ?? loaderData.items;

    const [search, setSearch] = useState('');
    const [selectedQuestionId, setSelectedQuestionId] = useState('');
    const [pendingRemoveItemId, setPendingRemoveItemId] = useState<
        string | null
    >(null);
    const [reordering, setReordering] = useState(false);
    const [uploadedTracks, setUploadedTracks] = useState<MusicTrackData[]>([]);
    const [creating, setCreating] = useState(false);
    const [newQuestionType, setNewQuestionType] =
        useState<QuestionType>('artist');
    const [newTrackId, setNewTrackId] = useState('');
    const [newPrompt, setNewPrompt] = useState('');
    const [newCorrectAnswer, setNewCorrectAnswer] = useState('');
    const [newDifficulty, setNewDifficulty] = useState(2);
    const [newBasePoints, setNewBasePoints] = useState(1000);
    const [newVisibility, setNewVisibility] = useState<
        (typeof Visibility)[keyof typeof Visibility]
    >(Visibility.Private);
    const [newMediaStart, setNewMediaStart] = useState('');
    const [newMediaEnd, setNewMediaEnd] = useState('');

    const allTracks = [...loaderTracks, ...uploadedTracks];

    const usedQuestionIds = new Set(items.map((i) => i.question_id));

    const addableQuestions = questions.filter(
        (q) => !usedQuestionIds.has(q.id),
    );

    const filteredItems = (() => {
        const q = search.trim().toLowerCase();
        if (!q) {
            return items;
        }
        return items.filter((item) => itemSearchBlob(item).includes(q));
    })();

    const selectedQuestionForAdd = (() => {
        if (selectedQuestionId.trim() === '') {
            return undefined;
        }
        return questions.find((qu) => qu.id === selectedQuestionId);
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
            setLocalItemsOverride({
                playlistId,
                loaderItemsFingerprint: itemsFingerprint,
                payload: result.data,
            });
        } else {
            toast.error('Could not reorder');
        }
    };

    const handleCreateNewQuestion = async () => {
        if (!newCorrectAnswer.trim()) {
            toast.error('Correct answer is required');
            return;
        }
        const mediaStart = parseNullableNonNegativeInt(newMediaStart);
        const mediaEnd = parseNullableNonNegativeInt(newMediaEnd);
        if (newMediaStart.trim() !== '' && mediaStart === null) {
            toast.error(
                'Media start must be a non-negative whole number of seconds',
            );
            return;
        }
        if (newMediaEnd.trim() !== '' && mediaEnd === null) {
            toast.error(
                'Media end must be a non-negative whole number of seconds',
            );
            return;
        }
        setCreating(true);
        const result = await createPlaylistQuestion(playlistId, {
            track_id: newTrackId.trim() === '' ? null : newTrackId.trim(),
            question_type: newQuestionType,
            prompt_text: newPrompt.trim() || null,
            correct_answer: newCorrectAnswer.trim(),
            base_points: newBasePoints,
            difficulty_level: newDifficulty,
            visibility: newVisibility,
            media_start_seconds: mediaStart,
            media_end_seconds: mediaEnd,
        });
        setCreating(false);
        if (result._tag === 'Success') {
            toast.success('Question added to playlist');
            setLocalItemsOverride({
                playlistId,
                loaderItemsFingerprint: itemsFingerprint,
                payload: result.data,
            });
            setNewCorrectAnswer('');
            setNewPrompt('');
            setNewTrackId('');
            setNewMediaStart('');
            setNewMediaEnd('');
            setNewVisibility(Visibility.Private);
            revalidator.revalidate();
        } else {
            toast.error('Could not create question');
        }
    };

    return (
        <PageShell>
            <PageHeader
                title={playlist.name}
                description={
                    <>
                        {playlist.description?.trim() ? (
                            <p className="mb-2 max-w-2xl">
                                {playlist.description}
                            </p>
                        ) : null}
                        <p className="text-xs">
                            {playlist.visibility} · Order:{' '}
                            {playlist.question_order} · Played{' '}
                            {playlist.play_count}×
                        </p>
                    </>
                }
            />

            <PageIntroExpandable
                summary="Add a new question (with optional audio) or pick from your library. Use Up and Down to change play order."
                moreLabel="How this playlist is used in a game"
            >
                <p className="text-muted text-sm">
                    Removing a question only takes it out of this playlist; it
                    stays in My quiz questions. When you host with this playlist
                    attached, rounds follow the order shown here (subject to the
                    playlist's order mode in the editor).
                </p>
            </PageIntroExpandable>

            <Surface variant="tint" className="mb-8 flex flex-col gap-4">
                <h2 className="text-lg font-semibold">
                    New question for this playlist
                </h2>
                <p className="text-muted text-sm">
                    Saves to your library and adds the bottom of this list. You
                    can reorder with Up / Down.
                </p>
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Question style
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={newQuestionType}
                            disabled={creating}
                            onChange={(e) =>
                                setNewQuestionType(
                                    e.target.value as QuestionType,
                                )
                            }
                        >
                            {questionTypes.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="sm:col-span-2">
                        <TrackPickerWithUpload
                            tracks={allTracks}
                            subCategories={sub_categories}
                            selectedTrackId={newTrackId}
                            onSelectedTrackIdChange={setNewTrackId}
                            onTrackCreated={(t) =>
                                setUploadedTracks((prev) => [...prev, t])
                            }
                            disabled={creating}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Prompt shown to players (optional)
                        </label>
                        <textarea
                            rows={2}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={newPrompt}
                            disabled={creating}
                            onChange={(e) => setNewPrompt(e.target.value)}
                            placeholder="e.g. Who performed this hit from 1984?"
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Correct answer
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={newCorrectAnswer}
                            disabled={creating}
                            onChange={(e) =>
                                setNewCorrectAnswer(e.target.value)
                            }
                            placeholder="Must match how you want responses scored"
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Difficulty (1–10)
                        </label>
                        <input
                            type="number"
                            min={1}
                            max={10}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={newDifficulty}
                            disabled={creating}
                            onChange={(e) =>
                                setNewDifficulty(
                                    Number.parseInt(e.target.value, 10) || 1,
                                )
                            }
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Base points
                        </label>
                        <input
                            type="number"
                            min={0}
                            max={100000}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={newBasePoints}
                            disabled={creating}
                            onChange={(e) =>
                                setNewBasePoints(
                                    Number.parseInt(e.target.value, 10) || 0,
                                )
                            }
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Visibility
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={newVisibility}
                            disabled={creating}
                            onChange={(e) =>
                                setNewVisibility(
                                    e.target
                                        .value as (typeof Visibility)[keyof typeof Visibility],
                                )
                            }
                        >
                            <option value={Visibility.Private}>
                                Private (only you)
                            </option>
                            <option value={Visibility.Draft}>Link-only</option>
                            <option value={Visibility.Public}>Public</option>
                        </select>
                    </div>
                    <details className="rounded-md border border-dashed border-transparent p-2 sm:col-span-2 dark:border-white/15">
                        <summary className="text-muted cursor-pointer text-sm font-medium">
                            Media trim (optional)
                        </summary>
                        <div className="mt-3 grid gap-3 sm:grid-cols-2">
                            <div>
                                <label className="text-muted mb-1 block text-xs font-medium">
                                    Start offset (seconds)
                                </label>
                                <input
                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                    value={newMediaStart}
                                    disabled={creating}
                                    onChange={(e) =>
                                        setNewMediaStart(e.target.value)
                                    }
                                    placeholder="Leave blank for full clip"
                                />
                            </div>
                            <div>
                                <label className="text-muted mb-1 block text-xs font-medium">
                                    End offset (seconds)
                                </label>
                                <input
                                    className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                    value={newMediaEnd}
                                    disabled={creating}
                                    onChange={(e) =>
                                        setNewMediaEnd(e.target.value)
                                    }
                                    placeholder="Leave blank for full clip"
                                />
                            </div>
                        </div>
                    </details>
                </div>
                <Button
                    type="button"
                    disabled={creating}
                    onClick={() => void handleCreateNewQuestion()}
                >
                    {creating ? 'Saving…' : 'Create and add to playlist'}
                </Button>
            </Surface>

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
                <h2 className="text-lg font-semibold">
                    Questions in this playlist
                </h2>
                {items.length === 0 ? (
                    <p className="text-muted">
                        No questions yet. Create one above or add from your
                        library below.
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
                                ? questionTypeLabel(
                                      questionTypes,
                                      q.question_type,
                                  )
                                : null;
                            return (
                                <li key={item.id} role="listitem">
                                    <Surface
                                        variant="tint"
                                        className="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-stretch sm:justify-between"
                                    >
                                        <div className="min-w-0 flex-1 space-y-1">
                                            <div className="font-medium">
                                                {q?.prompt_text?.trim()
                                                    ? q.prompt_text
                                                    : 'Untitled question'}
                                            </div>
                                            <div className="text-muted flex flex-wrap gap-x-2 gap-y-1 text-xs">
                                                {typeLabel ? (
                                                    <span className="bg-secondary/80 rounded px-1.5 py-0.5">
                                                        {typeLabel}
                                                    </span>
                                                ) : null}
                                            </div>
                                            {q?.correct_answer ? (
                                                <p className="text-muted line-clamp-2 text-sm">
                                                    Answer: {q.correct_answer}
                                                </p>
                                            ) : null}
                                            <QuizQuestionTrackAudioPlayerFromQuestion
                                                question={q ?? undefined}
                                                className="max-w-md"
                                            />
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
                                    </Surface>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </div>

            <details className="mb-8 overflow-hidden rounded-2xl bg-black/[0.025] dark:bg-white/[0.04]">
                <summary className="cursor-pointer list-none px-4 py-3 text-lg font-semibold marker:hidden [&::-webkit-details-marker]:hidden">
                    Add from library
                </summary>
                <div className="flex flex-col gap-4 border-t border-transparent px-4 pt-3 pb-4 dark:border-white/10">
                    {addableQuestions.length === 0 ? (
                        <p className="text-muted text-sm">
                            {questions.length === 0
                                ? 'You have no other questions yet. Use “New question” above.'
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
                                    <option value="">Select a question…</option>
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
                            {selectedQuestionForAdd ? (
                                <QuizQuestionTrackAudioPlayerFromQuestion
                                    question={selectedQuestionForAdd}
                                    className="max-w-md"
                                />
                            ) : null}
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
        </PageShell>
    );
}
