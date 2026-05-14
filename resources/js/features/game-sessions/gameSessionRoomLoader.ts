import { fetchGameSessionByRoomCode } from '@/features/game-sessions/api';
import { apiFailureMessage } from '@/lib/apiCore';
import { RouteLoaderResponseError } from '@/lib/routeLoaderResponseError';
import type { GameSessionRoomViewData } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';

const ROOM_CODE_PATTERN = /^[A-Za-z0-9]{6}$/;

const INVALID_ROOM_CODE_MESSAGE =
    'Room codes must be exactly 6 letters or numbers (A–Z, 0–9).';

export async function gameSessionRoomLoader({
    params,
}: {
    params: { roomCode?: string };
}): Promise<GameSessionRoomViewData> {
    const roomCode = params.roomCode;
    if (!roomCode) {
        throw new RouteLoaderResponseError(400, {
            message: 'Missing room code',
        });
    }
    if (!ROOM_CODE_PATTERN.test(roomCode.trim())) {
        throw new RouteLoaderResponseError(422, {
            message: INVALID_ROOM_CODE_MESSAGE,
        });
    }
    const result = await fetchGameSessionByRoomCode(roomCode);
    if (result._tag === 'Success') {
        return result.data;
    }
    if (result._tag === 'ValidationError') {
        throw new RouteLoaderResponseError(422, {
            message: apiFailureMessage(result, INVALID_ROOM_CODE_MESSAGE),
        });
    }
    if (result._tag === 'NotFoundError') {
        throw new RouteLoaderResponseError(404, { message: result.message });
    }
    if (result._tag === 'FatalError' && result.message.startsWith('HTTP 403')) {
        throw new RouteLoaderResponseError(403, { message: 'Forbidden' });
    }
    throw new RouteLoaderResponseError(500, {
        message: 'Could not load room',
    });
}
