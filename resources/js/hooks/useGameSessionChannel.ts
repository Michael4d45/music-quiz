import { useAuth } from '@/features/auth/AuthContext';
import { echoManager } from '@/lib/echoManager';
import { GameSessionParticipantJoinedDataSchema } from '@/schemas/App/Data/Events/GameSessionParticipantJoinedData';
import { Schema } from 'effect';
import { useEffect, useState } from 'react';

export function useGameSessionChannel(sessionId: string | undefined) {
    const { authState } = useAuth();
    const [participantCount, setParticipantCount] = useState<number | null>(
        null,
    );

    useEffect(() => {
        if (
            !sessionId ||
            !authState.hasFetchedUser ||
            !authState.isAuthenticated
        ) {
            return;
        }

        const channelName = `game-session.${sessionId}`;
        const callback = (data: unknown) => {
            const decoded = Schema.decodeUnknownEither(
                GameSessionParticipantJoinedDataSchema,
            )(data);
            if (decoded._tag === 'Right') {
                setParticipantCount(decoded.right.participant_count);
            }
        };

        echoManager.subscribeNotifications(
            channelName,
            'GameSessionParticipantJoined',
            callback,
        );

        return () => {
            echoManager.unsubscribeNotifications(
                channelName,
                'GameSessionParticipantJoined',
                callback,
            );
        };
    }, [authState.hasFetchedUser, authState.isAuthenticated, sessionId]);

    return { participantCount };
}
