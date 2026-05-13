import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { MessageResponseSchema } from '@/schemas/App/Data/MessageResponse';
import { MusicTrackDataSchema } from '@/schemas/App/Data/Models/MusicTrackData';
import { MyMusicTracksResponseDataSchema } from '@/schemas/App/Data/Responses/MyMusicTracksResponseData';
import type { MusicTrackOriginKind } from '@/schemas/App/Enums/MusicTrackOriginKind';
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
    origin_kind?: MusicTrackOriginKind | null;
    origin_title?: string | null;
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

export async function uploadMusicTrack(
    payload: {
        title: string;
        artist_name: string;
        album_name?: string | null;
        release_year?: number | null;
        genre?: string | null;
        duration_ms?: number | null;
        sub_category_id: string;
        origin_kind?: MusicTrackOriginKind | null;
        origin_title?: string | null;
    },
    audioFile: File,
) {
    const formData = new FormData();
    formData.append('audio', audioFile);
    formData.append('title', payload.title);
    formData.append('artist_name', payload.artist_name);
    formData.append('sub_category_id', payload.sub_category_id);
    if (payload.album_name != null && payload.album_name !== '') {
        formData.append('album_name', payload.album_name);
    }
    if (payload.release_year != null) {
        formData.append('release_year', String(payload.release_year));
    }
    if (payload.genre != null && payload.genre !== '') {
        formData.append('genre', payload.genre);
    }
    if (payload.duration_ms != null) {
        formData.append('duration_ms', String(payload.duration_ms));
    }
    if (payload.origin_kind != null) {
        formData.append('origin_kind', payload.origin_kind);
    }
    if (payload.origin_title != null && payload.origin_title !== '') {
        formData.append('origin_title', payload.origin_title);
    }

    return runEffect(
        pipe(
            Effect.succeed(formData),
            Effect.flatMap((fd) =>
                httpRequest('/api/my/music-tracks/upload', {
                    method: 'POST',
                    body: fd,
                }),
            ),
            withRetry('uploadMusicTrack'),
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
        origin_kind: MusicTrackOriginKind | null;
        origin_title: string | null;
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
