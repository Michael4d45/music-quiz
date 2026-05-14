import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { MessageResponseSchema } from '@/schemas/App/Data/MessageResponse';
import { QuizQuestionDataSchema } from '@/schemas/App/Data/Models/QuizQuestionData';
import { MyQuizQuestionsResponseDataSchema } from '@/schemas/App/Data/Responses/MyQuizQuestionsResponseData';
import { Effect, pipe } from 'effect';

export async function fetchMyQuizQuestions() {
    return runEffect(
        pipe(
            httpRequest('/api/my/quiz-questions'),
            withRetry('fetchMyQuizQuestions'),
            decodeJson(MyQuizQuestionsResponseDataSchema),
        ),
    );
}

export async function createQuizQuestion(payload: {
    track_id?: string | null;
    question_type: string;
    prompt_text?: string | null;
    correct_answer: string;
    base_points: number;
    media_start_seconds?: number | null;
    media_end_seconds?: number | null;
    difficulty_level: number;
    visibility?: string;
}) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest('/api/my/quiz-questions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('createQuizQuestion'),
            decodeJson(QuizQuestionDataSchema),
        ),
    );
}

export async function updateQuizQuestion(
    questionId: string,
    payload: Record<string, unknown>,
) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest(`/api/my/quiz-questions/${questionId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('updateQuizQuestion'),
            decodeJson(QuizQuestionDataSchema),
        ),
    );
}

export async function deleteQuizQuestion(questionId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/my/quiz-questions/${questionId}`, {
                method: 'DELETE',
            }),
            withRetry('deleteQuizQuestion'),
            decodeJson(MessageResponseSchema),
        ),
    );
}
