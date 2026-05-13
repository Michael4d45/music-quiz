import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { createQuizQuestion, fetchMyQuizQuestions } from '@/features/quiz-questions/api';
import type { MyQuizQuestionsResponseData } from '@/schemas/App/Data/Responses/MyQuizQuestionsResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useRevalidator } from 'react-router-dom';

export async function myQuizQuestionsLoader(): Promise<MyQuizQuestionsResponseData> {
    const result = await fetchMyQuizQuestions();
    if (result._tag === 'Success') {
        return result.data;
    }
    return { questions: [] };
}

export function MyQuizQuestionsPage() {
    const { questions } = useLoaderData<MyQuizQuestionsResponseData>();
    const revalidator = useRevalidator();
    const [correctAnswer, setCorrectAnswer] = useState('');
    const [promptText, setPromptText] = useState('');

    const handleCreate = async () => {
        if (!correctAnswer.trim()) {
            toast.error('Correct answer is required');
            return;
        }
        const result = await createQuizQuestion({
            question_type: 'artist',
            prompt_text: promptText.trim() || null,
            correct_answer: correctAnswer.trim(),
            base_points: 1000,
            difficulty_level: 2,
        });
        if (result._tag === 'Success') {
            toast.success('Question created');
            setCorrectAnswer('');
            setPromptText('');
            revalidator.revalidate();
        } else {
            toast.error('Could not create question');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">My quiz questions</h1>
                <ButtonLink to="/my/playlists" variant="secondary">
                    My playlists
                </ButtonLink>
            </div>

            <div className="bg-card mb-8 flex flex-col gap-3 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <p className="text-muted text-sm">
                    New questions default to type &quot;artist&quot;. You can attach
                    a track ID later via the API or admin tools.
                </p>
                <div>
                    <label
                        htmlFor="prompt"
                        className="text-muted mb-1 block text-sm font-medium"
                    >
                        Prompt (optional)
                    </label>
                    <input
                        id="prompt"
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={promptText}
                        onChange={(e) => setPromptText(e.target.value)}
                    />
                </div>
                <div>
                    <label
                        htmlFor="answer"
                        className="text-muted mb-1 block text-sm font-medium"
                    >
                        Correct answer
                    </label>
                    <input
                        id="answer"
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={correctAnswer}
                        onChange={(e) => setCorrectAnswer(e.target.value)}
                    />
                </div>
                <Button type="button" onClick={() => void handleCreate()}>
                    Create question
                </Button>
            </div>

            {questions.length === 0 ? (
                <p className="text-muted">No questions yet.</p>
            ) : (
                <ul className="flex flex-col gap-2" role="list">
                    {questions.map((q) => (
                        <li
                            key={q.id}
                            className="bg-card rounded-lg border border-transparent px-4 py-3 shadow-md dark:border-white/10"
                        >
                            <div className="font-mono text-xs text-muted">
                                {q.id}
                            </div>
                            <div className="font-medium">{q.question_type}</div>
                            <div className="text-muted text-sm">
                                {q.prompt_text ?? '—'}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
