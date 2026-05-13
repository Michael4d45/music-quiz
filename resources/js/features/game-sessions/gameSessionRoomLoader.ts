/* eslint-disable @typescript-eslint/only-throw-error -- React Router loaders throw data() for error boundaries */
import { fetchGameSessionByRoomCode } from '@/features/game-sessions/api';
import type { GameSessionData } from '@/schemas/App/Data/Models/GameSessionData';
import { data } from 'react-router-dom';

export async function gameSessionRoomLoader({
    params,
}: {
    params: { roomCode?: string };
}): Promise<GameSessionData> {
    const roomCode = params.roomCode;
    if (!roomCode) {
        throw data({ message: 'Missing room code' }, { status: 400 });
    }
    const result = await fetchGameSessionByRoomCode(roomCode);
    if (result._tag === 'Success') {
        return result.data;
    }
    if (result._tag === 'NotFoundError') {
        throw data({ message: result.message }, { status: 404 });
    }
    throw data({ message: 'Could not load room' }, { status: 500 });
}
