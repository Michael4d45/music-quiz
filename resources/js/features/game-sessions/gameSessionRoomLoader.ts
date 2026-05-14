/* eslint-disable @typescript-eslint/only-throw-error -- React Router loaders throw data() for error boundaries */
import { fetchGameSessionByRoomCode } from '@/features/game-sessions/api';
import { apiFailureMessage } from '@/lib/apiCore';
import type { GameSessionRoomViewData } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';
import { data } from 'react-router-dom';

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
        throw data({ message: 'Missing room code' }, { status: 400 });
    }
    if (!ROOM_CODE_PATTERN.test(roomCode.trim())) {
        throw data({ message: INVALID_ROOM_CODE_MESSAGE }, { status: 422 });
    }
    const result = await fetchGameSessionByRoomCode(roomCode);
    if (result._tag === 'Success') {
        return result.data;
    }
    if (result._tag === 'ValidationError') {
        throw data(
            { message: apiFailureMessage(result, INVALID_ROOM_CODE_MESSAGE) },
            { status: 422 },
        );
    }
    if (result._tag === 'NotFoundError') {
        throw data({ message: result.message }, { status: 404 });
    }
    if (result._tag === 'FatalError' && result.message.startsWith('HTTP 403')) {
        throw data({ message: 'Forbidden' }, { status: 403 });
    }
    throw data({ message: 'Could not load room' }, { status: 500 });
}
