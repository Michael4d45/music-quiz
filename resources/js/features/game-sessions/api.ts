import { decodeJson, httpRequest, runEffect, withRetry } from '@/lib/apiCore';
import { GameSessionDataSchema } from '@/schemas/App/Data/Models/GameSessionData';
import { GameSessionRoomViewDataSchema } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';
import { GameSessionsLobbyResponseDataSchema } from '@/schemas/App/Data/Models/GameSessionsLobbyResponseData';
import { SessionParticipantDataSchema } from '@/schemas/App/Data/Models/SessionParticipantData';
import { MessageResponseSchema } from '@/schemas/App/Data/MessageResponse';
import { MyGameSessionsResponseDataSchema } from '@/schemas/App/Data/Responses/MyGameSessionsResponseData';
import { QuizModesListResponseDataSchema } from '@/schemas/App/Data/Responses/QuizModesListResponseData';
import { ScoringRulesListResponseDataSchema } from '@/schemas/App/Data/Responses/ScoringRulesListResponseData';
import { Effect, pipe } from 'effect';

export async function fetchGameSessionsLobby() {
    return runEffect(
        pipe(
            httpRequest('/api/game-sessions/lobby'),
            withRetry('fetchGameSessionsLobby'),
            decodeJson(GameSessionsLobbyResponseDataSchema),
        ),
    );
}

export async function fetchGameSessionByRoomCode(roomCode: string) {
    const code = encodeURIComponent(roomCode.trim());
    return runEffect(
        pipe(
            httpRequest(`/api/game-sessions/room/${code}`),
            withRetry('fetchGameSessionByRoomCode'),
            decodeJson(GameSessionRoomViewDataSchema),
        ),
    );
}

export async function fetchGameSessionRecap(sessionId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/game-sessions/${sessionId}/recap`),
            withRetry('fetchGameSessionRecap'),
            decodeJson(GameSessionRoomViewDataSchema),
        ),
    );
}

export async function startGameSession(sessionId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/game-sessions/${sessionId}/start`, {
                method: 'POST',
            }),
            withRetry('startGameSession'),
            decodeJson(GameSessionRoomViewDataSchema),
        ),
    );
}

export async function advanceGameSessionRound(sessionId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/game-sessions/${sessionId}/advance-round`, {
                method: 'POST',
            }),
            withRetry('advanceGameSessionRound'),
            decodeJson(GameSessionRoomViewDataSchema),
        ),
    );
}

export async function submitSessionRoundAnswer(
    sessionId: string,
    roundId: string,
    payload: { submitted_text?: string; selected_option_id?: string },
) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest(
                    `/api/game-sessions/${sessionId}/rounds/${roundId}/answer`,
                    {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body),
                    },
                ),
            ),
            withRetry('submitSessionRoundAnswer'),
            decodeJson(GameSessionRoomViewDataSchema),
        ),
    );
}

export async function joinGameSession(roomCode: string) {
    return runEffect(
        pipe(
            Effect.succeed({ room_code: roomCode }),
            Effect.flatMap((body) =>
                httpRequest('/api/game-sessions/join', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('joinGameSession'),
            decodeJson(SessionParticipantDataSchema),
        ),
    );
}

export async function leaveGameSession(sessionId: string) {
    return runEffect(
        pipe(
            httpRequest(`/api/game-sessions/${sessionId}/leave`, {
                method: 'DELETE',
            }),
            withRetry('leaveGameSession'),
            decodeJson(MessageResponseSchema),
        ),
    );
}

export async function fetchMyGameSessions() {
    return runEffect(
        pipe(
            httpRequest('/api/my/game-sessions'),
            withRetry('fetchMyGameSessions'),
            decodeJson(MyGameSessionsResponseDataSchema),
        ),
    );
}

export async function fetchQuizModes() {
    return runEffect(
        pipe(
            httpRequest('/api/reference/quiz-modes'),
            withRetry('fetchQuizModes'),
            decodeJson(QuizModesListResponseDataSchema),
        ),
    );
}

export async function fetchScoringRules() {
    return runEffect(
        pipe(
            httpRequest('/api/reference/scoring-rules'),
            withRetry('fetchScoringRules'),
            decodeJson(ScoringRulesListResponseDataSchema),
        ),
    );
}

export async function createGameSession(payload: {
    quiz_mode_id: string;
    scoring_rule_id: string;
    playlist_id: string | null;
    max_players: number;
    is_public: boolean;
}) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest('/api/my/game-sessions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('createGameSession'),
            decodeJson(GameSessionDataSchema),
        ),
    );
}

export async function updateGameSession(
    sessionId: string,
    payload: Partial<{
        is_public: boolean;
        max_players: number;
        playlist_id: string | null;
    }>,
) {
    return runEffect(
        pipe(
            Effect.succeed(payload),
            Effect.flatMap((body) =>
                httpRequest(`/api/my/game-sessions/${sessionId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                }),
            ),
            withRetry('updateGameSession'),
            decodeJson(GameSessionDataSchema),
        ),
    );
}
