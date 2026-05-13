import ConfirmModal from '@/components/ConfirmModal';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import {
    createQuizQuestion,
    deleteQuizQuestion,
    fetchMyQuizQuestions,
} from '@/features/quiz-questions/api';
import { fetchMyMusicTracks } from '@/features/music-tracks/api';
import { fetchQuestionTypes } from '@/features/reference/api';
import type { IdLabelOptionData } from '@/schemas/App/Data/Models/IdLabelOptionData';
import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import type { MyQuizQuestionsResponseData } from '@/schemas/App/Data/Responses/MyQuizQuestionsResponseData';
import type { QuestionType } from '@/schemas/App/Enums/QuestionType';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { Link, useLoaderData, useRevalidator } from 'react-router-dom';

export interface MyQuizQuestionsLoaderData extends MyQuizQuestionsResponseData {
    readonly question_types: readonly IdLabelOptionData[];
    readonly tracks: readonly MusicTrackData[];
}

export async function myQuizQuestionsLoader(): Promise<MyQuizQuestionsLoaderData> {
    const [questionsRes, typesRes, tracksRes] = await Promise.all([
        fetchMyQuizQuestions(),
        fetchQuestionTypes(),
        fetchMyMusicTracks(),
    ]);

    return {
        questions:
            questionsRes._tag === 'Success' ? questionsRes.data.questions : [],
        question_types:
            typesRes._tag === 'Success' ? typesRes.data.question_types : [],
        tracks: tracksRes._tag === 'Success' ? tracksRes.data.tracks : [],
    };
}

function questionTypeLabel(
    types: readonly IdLabelOptionData[],
    value: QuestionType,
): string {
    const found = types.find((t) => t.id === value);
    return found?.label ?? value;
}

export function MyQuizQuestionsPage() {
    const { questions, question_types, tracks } =
        useLoaderData<MyQuizQuestionsLoaderData>();
    const revalidator = useRevalidator();
    const [questionType, setQuestionType] = useState<QuestionType>('artist');
    const [trackId, setTrackId] = useState('');
    const [correctAnswer, setCorrectAnswer] = useState('');
    const [promptText, setPromptText] = useState('');
    const [difficultyLevel, setDifficultyLevel] = useState(2);
    const [basePoints, setBasePoints] = useState(1000);
    const [pendingDeleteId, setPendingDeleteId] = useState<string | null>(null);

    const handleCreate = async () => {
        if (!correctAnswer.trim()) {
            toast.error('Correct answer is required');
            return;
        }
        const result = await createQuizQuestion({
            track_id: trackId.trim() === '' ? null : trackId.trim(),
            question_type: questionType,
            prompt_text: promptText.trim() || null,
            correct_answer: correctAnswer.trim(),
            base_points: basePoints,
            difficulty_level: difficultyLevel,
        });
        if (result._tag === 'Success') {
            toast.success('Question created');
            setCorrectAnswer('');
            setPromptText('');
            setTrackId('');
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
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">My quiz questions</h1>
                <div className="flex flex-wrap gap-2">
                    <ButtonLink to="/my/music-tracks" variant="secondary">
                        My tracks
                    </ButtonLink>
                    <ButtonLink to="/my/playlists" variant="secondary">
                        My playlists
                    </ButtonLink>
                </div>
            </div>

            <p className="text-muted mb-6 max-w-2xl text-sm">
                Each question stores the right answer and how it should be
                played. Link one of your tracks when the round should reference a
                specific recording (title, year, artist, and so on).
            </p>

            <div className="bg-card mb-8 flex flex-col gap-4 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <h2 className="text-lg font-semibold">Create a question</h2>
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
                    <div>
                        <label className="text-muted mb-1 block text-sm font-medium">
                            Linked track (optional)
                        </label>
                        <select
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={trackId}
                            onChange={(e) => setTrackId(e.target.value)}
                        >
                            <option value="">None — standalone question</option>
                            {tracks.map((t) => (
                                <option key={t.id} value={t.id}>
                                    {t.title} — {t.artist_name}
                                </option>
                            ))}
                        </select>
                        {tracks.length === 0 && (
                            <p className="text-muted mt-1 text-xs">
                                Add tracks under{' '}
                                <Link
                                    to="/my/music-tracks"
                                    className="text-primary hover:text-primary-hover font-medium underline"
                                >
                                    My tracks
                                </Link>{' '}
                                to enable linking.
                            </p>
                        )}
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
                </div>
                <Button type="button" onClick={() => void handleCreate()}>
                    Create question
                </Button>
            </div>

            {questions.length === 0 ? (
                <p className="text-muted">No questions yet.</p>
            ) : (
                <ul className="flex flex-col gap-3" role="list">
                    {questions.map((q) => (
                        <li
                            key={q.id}
                            className="bg-card flex flex-col gap-2 rounded-lg border border-transparent px-4 py-3 shadow-md sm:flex-row sm:items-start sm:justify-between dark:border-white/10"
                        >
                            <div className="min-w-0 flex-1 space-y-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="bg-primary-light text-primary rounded px-2 py-0.5 text-xs font-semibold uppercase tracking-wide">
                                        {questionTypeLabel(
                                            question_types,
                                            q.question_type,
                                        )}
                                    </span>
                                    {q.track && (
                                        <span className="text-muted text-sm">
                                            {q.track.title} — {q.track.artist_name}
                                        </span>
                                    )}
                                    {!q.track && (
                                        <span className="text-muted text-sm">
                                            No linked track
                                        </span>
                                    )}
                                </div>
                                <div className="font-medium">
                                    {q.prompt_text?.trim()
                                        ? q.prompt_text
                                        : '—'}
                                </div>
                                <div className="text-sm">
                                    <span className="text-muted">Answer: </span>
                                    <span className="font-mono">
                                        {q.correct_answer}
                                    </span>
                                </div>
                                <div className="text-muted text-xs">
                                    Points {q.base_points} · difficulty{' '}
                                    {q.difficulty_level}
                                </div>
                            </div>
                            <Button
                                type="button"
                                variant="danger"
                                className="shrink-0 self-start sm:ml-4"
                                onClick={() => setPendingDeleteId(q.id)}
                            >
                                Delete
                            </Button>
                        </li>
                    ))}
                </ul>
            )}

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
