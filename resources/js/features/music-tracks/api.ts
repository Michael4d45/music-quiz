import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { MessageResponseSchema } from '@/schemas/App/Data/MessageResponse';
import { MusicTrackDataSchema } from '@/schemas/App/Data/Models/MusicTrackData';
import { MyMusicTracksResponseDataSchema } from '@/schemas/App/Data/Responses/MyMusicTracksResponseData';
import { Effect, pipe } from 'effect';

export async function fetchMyMusicTracks() {
    return runEffect(
        pipe(
            httpRequest('/api/my/music-tracks'),
            withRetry('fetchMyMusicTracks'),
            decodeJson(MyMusicTracksResponseDataSchema),
        ),
    );
}

export async function createMusicTrack(payload: {
    title: string;
    artist_name: string;
    album_name?: string | null;
    release_year?: number | null;
    genre?: string | null;
    duration_ms?: number | null;
    sub_category_id: string;
    primary_source_id: string;
}) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest('/api/my/music-tracks', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('createMusicTrack'),
            decodeJson(MusicTrackDataSchema),
        ),
    );
}

export async function updateMusicTrack(
    trackId: string,
    payload: Partial<{
        title: string;
        artist_name: string;
        album_name: string | null;
        release_year: number | null;
        genre: string | null;
        duration_ms: number | null;
        sub_category_id: string;
        primary_source_id: string;
    }>,
) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest(`/api/my/music-tracks/${trackId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('updateMusicTrack'),
            decodeJson(MusicTrackDataSchema),
        ),
    );
}

export async function deleteMusicTrack(trackId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/my/music-tracks/${trackId}`, {
                method: 'DELETE',
            }),
            withRetry('deleteMusicTrack'),
            decodeJson(MessageResponseSchema),
        ),
    );
}
