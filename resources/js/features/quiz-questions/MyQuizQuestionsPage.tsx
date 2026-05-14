import ConfirmModal from '@/components/ConfirmModal';
import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import {
    createQuizQuestion,
    deleteQuizQuestion,
    fetchMyQuizQuestions,
    updateQuizQuestion,
} from '@/features/quiz-questions/api';
import { fetchMyMusicTracks } from '@/features/music-tracks/api';
import {
    questionGroupHeading,
    sortQuestionGroupEntries,
} from '@/features/music-tracks/trackGrouping';
import { QuizQuestionTrackAudioPlayer } from '@/features/quiz-questions/QuizQuestionTrackAudioPlayer';
import { TrackPickerWithUpload } from '@/features/quiz-questions/TrackPickerWithUpload';
import { fetchQuestionTypes, fetchSubCategories } from '@/features/reference/api';
import { cn } from '@/lib/utils';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import type { MyQuizQuestionsResponseData } from '@/schemas/App/Data/Responses/MyQuizQuestionsResponseData';
import type { QuizQuestionData } from '@/schemas/App/Data/Models/QuizQuestionData';
import type { QuestionType } from '@/schemas/App/Enums/QuestionType';
import { Visibility } from '@/schemas/App/Enums/Visibility';
import { useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export interface MyQuizQuestionsLoaderData extends MyQuizQuestionsResponseData {
    readonly question_types: readonly IdLabelOptionData[];
    readonly tracks: readonly MusicTrackData[];
    readonly sub_categories: readonly IdLabelOptionData[];
}

const VISIBILITY_OPTIONS: { value: (typeof Visibility)[keyof typeof Visibility]; label: string }[] = [
    { value: Visibility.Private, label: 'Private (only you)' },
    { value: Visibility.Draft, label: 'Link-only' },
    { value: Visibility.Public, label: 'Public' },
];

export async function myQuizQuestionsLoader(): Promise<MyQuizQuestionsLoaderData> {
    const [questionsRes, typesRes, tracksRes, subRes] = await Promise.all([
        fetchMyQuizQuestions(),
        fetchQuestionTypes(),
        fetchMyMusicTracks(),
        fetchSubCategories(),
    ]);

    return {
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

export function MyQuizQuestionsPage() {
    const { questions, question_types, tracks, sub_categories } =
        useLoaderData<MyQuizQuestionsLoaderData>();
    const revalidator = useRevalidator();
    const [uploadedTracks, setUploadedTracks] = useState<MusicTrackData[]>([]);
    const [questionType, setQuestionType] = useState<QuestionType>('artist');
    const [trackId, setTrackId] = useState('');
    const [correctAnswer, setCorrectAnswer] = useState('');
    const [promptText, setPromptText] = useState('');
    const [difficultyLevel, setDifficultyLevel] = useState(2);
    const [basePoints, setBasePoints] = useState(1000);
    const [visibility, setVisibility] = useState<
        (typeof Visibility)[keyof typeof Visibility]
    >(Visibility.Private);
    const [mediaStartSeconds, setMediaStartSeconds] = useState('');
    const [mediaEndSeconds, setMediaEndSeconds] = useState('');
    const [search, setSearch] = useState('');
    const [pendingDeleteId, setPendingDeleteId] = useState<string | null>(null);

    const allTracks = [...tracks, ...uploadedTracks];

    const filteredQuestions = (() => {
        const q = search.trim().toLowerCase();
        if (!q) {
            return questions;
        }
        return questions.filter((item) => {
            const blob = [
                item.prompt_text ?? '',
                item.correct_answer,
                item.track?.title ?? '',
                item.track?.artist_name ?? '',
                questionTypeLabel(question_types, item.question_type),
            ]
                .join(' ')
                .toLowerCase();
            return blob.includes(q);
        });
    })();

    const groupedQuestions = (() => {
        const map = new Map<string, QuizQuestionData[]>();
        for (const item of filteredQuestions) {
            const heading = questionGroupHeading(item);
            const list = map.get(heading) ?? [];
            list.push(item);
            map.set(heading, list);
        }
        for (const list of map.values()) {
            list.sort(
                (a, b) =>
                    (b.updated_at?.getTime() ?? 0) -
                    (a.updated_at?.getTime() ?? 0),
            );
        }
        return sortQuestionGroupEntries([...map.entries()]);
    })();

    const handleCreate = async () => {
        if (!correctAnswer.trim()) {
            toast.error('Correct answer is required');
            return;
        }
        const mediaStart = parseNullableNonNegativeInt(mediaStartSeconds);
        const mediaEnd = parseNullableNonNegativeInt(mediaEndSeconds);
        if (mediaStartSeconds.trim() !== '' && mediaStart === null) {
            toast.error('Media start must be a non-negative whole number of seconds');
            return;
        }
        if (mediaEndSeconds.trim() !== '' && mediaEnd === null) {
            toast.error('Media end must be a non-negative whole number of seconds');
            return;
        }

        const result = await createQuizQuestion({
            track_id: trackId.trim() === '' ? null : trackId.trim(),
            question_type: questionType,
            prompt_text: promptText.trim() || null,
            correct_answer: correctAnswer.trim(),
            base_points: basePoints,
            difficulty_level: difficultyLevel,
            visibility,
            media_start_seconds: mediaStart,
            media_end_seconds: mediaEnd,
        });
        if (result._tag === 'Success') {
            toast.success('Question created');
            setCorrectAnswer('');
            setPromptText('');
            setTrackId('');
            setMediaStartSeconds('');
            setMediaEndSeconds('');
            setVisibility(Visibility.Private);
            revalidator.revalidate();
        } else {
            toast.error('Could not create question');
        }
    };

    const handleConfirmDelete = async (id: string) => {
        const result = await deleteQuizQuestion(id);
        if (result._tag === 'Success') {
            toast.success('Question deleted');
            revalidator.revalidate();
        } else {
            toast.error('Could not delete question');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <h1 className="mb-6 text-2xl font-bold">My quiz questions</h1>

            <PageIntroExpandable
                summary="Write prompts and correct answers; link a track when a round needs a specific recording."
                moreLabel="More about quiz questions"
            >
                <p>
                    Build prompts and the canonical correct answer. Pick an
                    existing track or upload a short clip below; groups in the
                    track list mirror My tracks. Uploaded clips can be previewed
                    while you tune wording or media trim.
                </p>
            </PageIntroExpandable>

            <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label className="flex min-w-0 flex-1 flex-col gap-1 text-sm">
                    <span className="text-muted font-medium">Search</span>
                    <input
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Prompt, answer, track, or question style…"
                        autoComplete="off"
                    />
                </label>
            </div>

            <div className="mb-8 flex flex-col gap-4">
                <h2 className="text-lg font-semibold">Question library</h2>
                {groupedQuestions.length === 0 ? (
                    <p className="text-muted">
                        {questions.length === 0
                            ? 'No questions yet. Add your first one below.'
                            : 'No questions match your search.'}
                    </p>
                ) : (
                    <div className="flex flex-col gap-3">
                        {groupedQuestions.map(([heading, list]) => (
                            <details
                                key={heading}
                                className="bg-card rounded-lg border border-transparent shadow-md open:border-transparent dark:border-white/10"
                            >
                                <summary className="cursor-pointer list-none rounded-lg px-4 py-3 marker:hidden [&::-webkit-details-marker]:hidden">
                                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                                        <span className="font-semibold">
                                            {heading}
                                        </span>
                                        <span className="text-muted text-sm">
                                            {list.length}{' '}
                                            {list.length === 1
                                                ? 'question'
                                                : 'questions'}
                                        </span>
                                    </div>
                                </summary>
                                <ul
                                    className="flex flex-col gap-2 border-t border-transparent px-2 pb-3 pt-1 dark:border-white/10"
                                    role="list"
                                >
                                    {list.map((q) => (
                                        <li key={q.id}>
                                            <QuizQuestionRow
                                                key={`${q.id}-${q.updated_at?.toString() ?? ''}`}
                                                question={q}
                                                question_types={question_types}
                                                tracks={allTracks}
                                                sub_categories={sub_categories}
                                                onTrackCreated={(t) =>
                                                    setUploadedTracks((prev) => [
                                                        ...prev,
                                                        t,
                                                    ])
                                                }
                                                onSaved={() =>
                                                    revalidator.revalidate()
                                                }
                                                onRequestDelete={() =>
                                                    setPendingDeleteId(q.id)
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
                    Create a question
                </summary>
                <div className="flex flex-col gap-4 border-t border-transparent px-4 pb-4 pt-3 dark:border-white/10">
                    <div className="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label className="text-muted mb-1 block text-sm font-medium">
                                Question style
                            </label>
                            <select
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={questionType}
                                onChange={(e) =>
                                    setQuestionType(e.target.value as QuestionType)
                                }
                            >
                                {question_types.map((opt) => (
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
                                selectedTrackId={trackId}
                                onSelectedTrackIdChange={setTrackId}
                                onTrackCreated={(t) =>
                                    setUploadedTracks((prev) => [...prev, t])
                                }
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <label className="text-muted mb-1 block text-sm font-medium">
                                Prompt shown to players (optional)
                            </label>
                            <textarea
                                id="prompt"
                                rows={2}
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={promptText}
                                onChange={(e) => setPromptText(e.target.value)}
                                placeholder="e.g. Who performed this hit from 1984?"
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <label className="text-muted mb-1 block text-sm font-medium">
                                Correct answer
                            </label>
                            <input
                                id="answer"
                                className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                                value={correctAnswer}
                                onChange={(e) => setCorrectAnswer(e.target.value)}
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
                                value={difficultyLevel}
                                onChange={(e) =>
                                    setDifficultyLevel(
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
                                value={basePoints}
                                onChange={(e) =>
                                    setBasePoints(
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
                        <details className="sm:col-span-2 rounded-md border border-dashed border-transparent p-2 dark:border-white/15">
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
                                        value={mediaStartSeconds}
                                        onChange={(e) =>
                                            setMediaStartSeconds(e.target.value)
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
                                        value={mediaEndSeconds}
                                        onChange={(e) =>
                                            setMediaEndSeconds(e.target.value)
                                        }
                                        placeholder="Leave blank for full clip"
                                    />
                                </div>
                            </div>
                        </details>
                    </div>
                    <Button type="button" onClick={() => void handleCreate()}>
                        Create question
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
                title="Delete this question?"
                message="This removes the question from your library. Playlists that already reference it may need to be updated separately."
                confirmText="Delete"
                cancelText="Cancel"
            />
        </div>
    );
}

interface QuizQuestionRowProps {
    readonly question: QuizQuestionData;
    readonly question_types: readonly IdLabelOptionData[];
    readonly tracks: readonly MusicTrackData[];
    readonly sub_categories: readonly IdLabelOptionData[];
    readonly onTrackCreated: (track: MusicTrackData) => void;
    readonly onSaved: () => void;
    readonly onRequestDelete: () => void;
}

function QuizQuestionRow({
    question: q,
    question_types,
    tracks,
    sub_categories,
    onTrackCreated,
    onSaved,
    onRequestDelete,
}: QuizQuestionRowProps) {
    const [questionType, setQuestionType] = useState<QuestionType>(q.question_type);
    const [trackId, setTrackId] = useState(q.track_id ?? '');
    const [promptText, setPromptText] = useState(q.prompt_text ?? '');
    const [correctAnswer, setCorrectAnswer] = useState(q.correct_answer);
    const [difficultyLevel, setDifficultyLevel] = useState(q.difficulty_level);
    const [basePoints, setBasePoints] = useState(q.base_points);
    const [visibility, setVisibility] = useState(q.visibility);
    const [mediaStartSeconds, setMediaStartSeconds] = useState(
        q.media_start_seconds != null ? String(q.media_start_seconds) : '',
    );
    const [mediaEndSeconds, setMediaEndSeconds] = useState(
        q.media_end_seconds != null ? String(q.media_end_seconds) : '',
    );
    const [saving, setSaving] = useState(false);

    const displayTrack = (() => {
        if (trackId.trim() === '') {
            return null;
        }
        return tracks.find((t) => t.id === trackId) ?? q.track ?? null;
    })();

    const handleSave = async () => {
        if (!correctAnswer.trim()) {
            toast.error('Correct answer is required');
            return;
        }
        const mediaStart = parseNullableNonNegativeInt(mediaStartSeconds);
        const mediaEnd = parseNullableNonNegativeInt(mediaEndSeconds);
        if (mediaStartSeconds.trim() !== '' && mediaStart === null) {
            toast.error('Media start must be a non-negative whole number of seconds');
            return;
        }
        if (mediaEndSeconds.trim() !== '' && mediaEnd === null) {
            toast.error('Media end must be a non-negative whole number of seconds');
            return;
        }

        setSaving(true);
        const result = await updateQuizQuestion(q.id, {
            track_id: trackId.trim() === '' ? null : trackId.trim(),
            question_type: questionType,
            prompt_text: promptText.trim() || null,
            correct_answer: correctAnswer.trim(),
            base_points: basePoints,
            difficulty_level: difficultyLevel,
            visibility,
            media_start_seconds: mediaStart,
            media_end_seconds: mediaEnd,
        });
        setSaving(false);

        if (result._tag === 'Success') {
            toast.success('Question updated');
            onSaved();
        } else {
            toast.error('Could not update question');
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
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="bg-primary-light text-primary rounded px-2 py-0.5 text-xs font-semibold uppercase tracking-wide">
                                {questionTypeLabel(question_types, questionType)}
                            </span>
                            {displayTrack ? (
                                <span className="text-muted text-sm">
                                    {displayTrack.title} — {displayTrack.artist_name}
                                </span>
                            ) : (
                                <span className="text-muted text-sm">
                                    No linked track
                                </span>
                            )}
                            <span className="text-muted rounded border border-transparent px-1.5 py-0.5 text-xs dark:border-white/15">
                                {visibility}
                            </span>
                        </div>
                        <div className="font-medium">
                            {promptText.trim() ? promptText : '—'}
                        </div>
                        <div className="text-sm">
                            <span className="text-muted">Answer: </span>
                            <span className="font-mono">{correctAnswer}</span>
                        </div>
                        <div className="text-muted text-xs">
                            Points {basePoints} · difficulty {difficultyLevel}
                        </div>
                    </div>
                    <span className="text-muted shrink-0 text-xs sm:pt-0.5">
                        Tap to edit
                    </span>
                </div>
                {trackId.trim() !== '' ? (
                    <div
                        className="border-t border-transparent pt-2 dark:border-white/10"
                        onClick={(e) => e.stopPropagation()}
                        onKeyDown={(e) => e.stopPropagation()}
                        role="presentation"
                    >
                        <QuizQuestionTrackAudioPlayer
                            trackId={trackId}
                            track={displayTrack ?? undefined}
                        />
                    </div>
                ) : null}
            </summary>
            <div className="flex flex-col gap-3 border-t border-transparent px-3 pb-3 pt-2 dark:border-white/10">
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Question style
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={questionType}
                            onChange={(e) =>
                                setQuestionType(e.target.value as QuestionType)
                            }
                        >
                            {question_types.map((opt) => (
                                <option key={opt.id} value={opt.id}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Audio
                        </label>
                        <TrackPickerWithUpload
                            tracks={tracks}
                            subCategories={sub_categories}
                            selectedTrackId={trackId}
                            onSelectedTrackIdChange={setTrackId}
                            onTrackCreated={onTrackCreated}
                            disabled={saving}
                            selectLabel="Linked track"
                            noneOptionLabel="None — standalone"
                            emptyTracksHint={null}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Prompt
                        </label>
                        <textarea
                            rows={2}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={promptText}
                            onChange={(e) => setPromptText(e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Correct answer
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={correctAnswer}
                            onChange={(e) => setCorrectAnswer(e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Difficulty (1–10)
                        </label>
                        <input
                            type="number"
                            min={1}
                            max={10}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={difficultyLevel}
                            onChange={(e) =>
                                setDifficultyLevel(
                                    Number.parseInt(e.target.value, 10) || 1,
                                )
                            }
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Base points
                        </label>
                        <input
                            type="number"
                            min={0}
                            max={100000}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={basePoints}
                            onChange={(e) =>
                                setBasePoints(
                                    Number.parseInt(e.target.value, 10) || 0,
                                )
                            }
                        />
                    </div>
                    <div className="sm:col-span-2">
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
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Media start (seconds)
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={mediaStartSeconds}
                            onChange={(e) => setMediaStartSeconds(e.target.value)}
                            placeholder="Optional"
                        />
                    </div>
                    <div>
                        <label className="text-muted mb-1 block text-xs font-medium">
                            Media end (seconds)
                        </label>
                        <input
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={mediaEndSeconds}
                            onChange={(e) => setMediaEndSeconds(e.target.value)}
                            placeholder="Optional"
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
