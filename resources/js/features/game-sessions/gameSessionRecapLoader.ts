import { fetchGameSessionRecap } from '@/features/game-sessions/api';
import { RouteLoaderResponseError } from '@/lib/routeLoaderResponseError';
import type { GameSessionRoomViewData } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';

export async function gameSessionRecapLoader({
    params,
}: {
    params: { sessionId?: string };
}): Promise<GameSessionRoomViewData> {
    const sessionId = params.sessionId;
    if (!sessionId) {
        throw new RouteLoaderResponseError(400, { message: 'Missing session' });
    }
    const result = await fetchGameSessionRecap(sessionId);
    if (result._tag === 'Success') {
        return result.data;
    }
    if (result._tag === 'NotFoundError') {
        throw new RouteLoaderResponseError(404, { message: result.message });
    }
    if (result._tag === 'AuthenticationError') {
        throw new RouteLoaderResponseError(401, { message: result.message });
    }
    if (result._tag === 'FatalError' && result.message.startsWith('HTTP 403')) {
        throw new RouteLoaderResponseError(403, { message: 'Forbidden' });
    }
    throw new RouteLoaderResponseError(500, {
        message: 'Could not load recap',
    });
}
