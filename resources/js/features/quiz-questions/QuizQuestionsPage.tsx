import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { QuizQuestionsResponse } from '@/schemas/App/Data/Response';
import { Link, redirect, useLoaderData } from 'react-router-dom';

export async function quizQuestionsLoader() {
    let user = authManager.getUser();
    let token = authManager.getToken();

    // Try to restore from session if no local token
    if (!user || !token) {
        const result = await ApiClient.fetchSessionToken();
        if (result._tag === 'Success') {
            authManager.setAuthData(result.data.token, result.data.user);
            user = result.data.user;
            token = result.data.token;
        }
    }

    if (!user || !token) {
        return redirect('/login');
    }

    const result = await ApiClient.listQuizQuestions();
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load quiz questions');
}

export function QuizQuestionsPage() {
    const data = useLoaderData<QuizQuestionsResponse>();

    const getQuestionTypeLabel = (type: string) => {
        switch (type) {
            case 'artist':
                return 'Artist';
            case 'title':
                return 'Title';
            case 'year':
                return 'Year';
            case 'multiple_choice':
                return 'Multiple Choice';
            case 'lyric':
                return 'Lyric';
            case 'audio_clip':
                return 'Audio Clip';
            default:
                return type;
        }
    };

    const getDifficultyLabel = (level: number) => {
        switch (level) {
            case 1:
                return 'Easy';
            case 2:
                return 'Medium';
            case 3:
                return 'Hard';
            case 4:
                return 'Expert';
            case 5:
                return 'Master';
            default:
                return `Level ${level}`;
        }
    };

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mb-8 flex items-center justify-between">
                <h1 className="text-3xl font-bold">My Quiz Questions</h1>
                <Link
                    to="/quiz-questions/create"
                    className="btn-info rounded-lg px-4 py-2 transition-colors"
                >
                    Create Question
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data.quiz_questions.data.map((question) => (
                    <div
                        key={question.id}
                        className="bg-card rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
                    >
                        <div className="mb-4 flex items-start justify-between">
                            <span className="bg-muted rounded px-2 py-1 text-xs">
                                {getQuestionTypeLabel(
                                    question.question_type as string,
                                )}
                            </span>
                            <span className="bg-muted rounded px-2 py-1 text-xs">
                                {getDifficultyLabel(question.difficulty_level)}
                            </span>
                        </div>

                        {question.prompt_text && (
                            <p className="text-muted mb-2 line-clamp-2">
                                {question.prompt_text}
                            </p>
                        )}

                        <p className="mb-2 text-sm">
                            <span className="font-medium">Answer:</span>{' '}
                            {question.correct_answer}
                        </p>

                        {(question.track as any) && (
                            <p className="text-muted mb-2 text-sm">
                                Track: {(question.track as any)?.title} by{' '}
                                {(question.track as any)?.artist_name}
                            </p>
                        )}

                        <div className="text-muted mt-4 flex items-center gap-4 text-sm">
                            <span>{question.base_points} points</span>
                            <span
                                className={
                                    question.visibility === 'public'
                                        ? 'text-green-600'
                                        : 'text-orange-600'
                                }
                            >
                                {question.visibility === 'public'
                                    ? 'Public'
                                    : 'Private'}
                            </span>
                        </div>
                    </div>
                ))}
            </div>

            {data.quiz_questions.data.length === 0 && (
                <div className="py-12 text-center">
                    <p className="text-muted mb-4">
                        You don't have any quiz questions yet.
                    </p>
                    <Link
                        to="/quiz-questions/create"
                        className="text-(--info) hover:underline"
                    >
                        Create your first question
                    </Link>
                </div>
            )}
        </div>
    );
}
