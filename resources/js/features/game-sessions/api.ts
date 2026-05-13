import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { GameSessionsLobbyResponseDataSchema } from '@/schemas/App/Data/Models/GameSessionsLobbyResponseData';
import { pipe } from 'effect';

export async function fetchGameSessionsLobby() {
    return runEffect(
        pipe(
            httpRequest('/api/game-sessions/lobby'),
            withRetry('fetchGameSessionsLobby'),
            decodeJson(GameSessionsLobbyResponseDataSchema),
        ),
    );
}
