import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { MusicSourcesListResponseDataSchema } from '@/schemas/App/Data/Responses/MusicSourcesListResponseData';
import { QuestionTypesListResponseDataSchema } from '@/schemas/App/Data/Responses/QuestionTypesListResponseData';
import { SubCategoriesListResponseDataSchema } from '@/schemas/App/Data/Responses/SubCategoriesListResponseData';
import { pipe } from 'effect';

export async function fetchSubCategories() {
    return runEffect(
        pipe(
            httpRequest('/api/reference/sub-categories'),
            withRetry('fetchSubCategories'),
            decodeJson(SubCategoriesListResponseDataSchema),
        ),
    );
}

export async function fetchMusicSources() {
    return runEffect(
        pipe(
            httpRequest('/api/reference/music-sources'),
            withRetry('fetchMusicSources'),
            decodeJson(MusicSourcesListResponseDataSchema),
        ),
    );
}

export async function fetchQuestionTypes() {
    return runEffect(
        pipe(
            httpRequest('/api/reference/question-types'),
            withRetry('fetchQuestionTypes'),
            decodeJson(QuestionTypesListResponseDataSchema),
        ),
    );
}
