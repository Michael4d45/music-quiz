import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { MessageResponseSchema } from '@/schemas/App/Data/MessageResponse';
import { PlaylistDataSchema } from '@/schemas/App/Data/Models/PlaylistData';
import { PlaylistItemDataSchema } from '@/schemas/App/Data/Models/PlaylistItemData';
import { MyPlaylistItemsResponseDataSchema } from '@/schemas/App/Data/Responses/MyPlaylistItemsResponseData';
import { MyPlaylistsResponseDataSchema } from '@/schemas/App/Data/Responses/MyPlaylistsResponseData';
import { Effect, pipe } from 'effect';

export async function fetchMyPlaylists() {
    return runEffect(
        pipe(
            httpRequest('/api/my/playlists'),
            withRetry('fetchMyPlaylists'),
            decodeJson(MyPlaylistsResponseDataSchema),
        ),
    );
}

export async function createPlaylist(payload: {
    name: string;
    description?: string | null;
}) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest('/api/my/playlists', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('createPlaylist'),
            decodeJson(PlaylistDataSchema),
        ),
    );
}

export async function updatePlaylist(
    playlistId: string,
    payload: Partial<{
        name: string;
        description: string | null;
        status: string;
        visibility: string;
    }>,
) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest(`/api/my/playlists/${playlistId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('updatePlaylist'),
            decodeJson(PlaylistDataSchema),
        ),
    );
}

export async function deletePlaylist(playlistId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/my/playlists/${playlistId}`, {
                method: 'DELETE',
            }),
            withRetry('deletePlaylist'),
            decodeJson(MessageResponseSchema),
        ),
    );
}

export async function fetchPlaylistItems(playlistId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/my/playlists/${playlistId}/items`),
            withRetry('fetchPlaylistItems'),
            decodeJson(MyPlaylistItemsResponseDataSchema),
        ),
    );
}

export async function addPlaylistItem(
    playlistId: string,
    questionId: string,
) {
    return runEffect(
        pipe(
            Effect.succeed({ question_id: questionId }),
            Effect.flatMap((body) =>
                httpRequest(`/api/my/playlists/${playlistId}/items`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('addPlaylistItem'),
            decodeJson(PlaylistItemDataSchema),
        ),
    );
}

export async function removePlaylistItem(
    playlistId: string,
    playlistItemId: string,
) {
    return runEffect(
        pipe(
            httpRequest(
                `/api/my/playlists/${playlistId}/items/${playlistItemId}`,
                { method: 'DELETE' },
            ),
            withRetry('removePlaylistItem'),
            decodeJson(MessageResponseSchema),
        ),
    );
}
