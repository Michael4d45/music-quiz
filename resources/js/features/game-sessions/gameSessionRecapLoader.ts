/* eslint-disable @typescript-eslint/only-throw-error -- React Router loaders throw data() for error boundaries */
import { fetchGameSessionRecap } from '@/features/game-sessions/api';
import type { GameSessionRoomViewData } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';
import { data } from 'react-router-dom';

export async function gameSessionRecapLoader({
    params,
}: {
    params: { sessionId?: string };
}): Promise<GameSessionRoomViewData> {
    const sessionId = params.sessionId;
    if (!sessionId) {
        throw data({ message: 'Missing session' }, { status: 400 });
    }
    const result = await fetchGameSessionRecap(sessionId);
    if (result._tag === 'Success') {
        return result.data;
    }
    if (result._tag === 'NotFoundError') {
        throw data({ message: result.message }, { status: 404 });
    }
    if (result._tag === 'AuthenticationError') {
        throw data({ message: result.message }, { status: 401 });
    }
    if (result._tag === 'FatalError' && result.message.startsWith('HTTP 403')) {
        throw data({ message: 'Forbidden' }, { status: 403 });
    }
    throw data({ message: 'Could not load recap' }, { status: 500 });
}
