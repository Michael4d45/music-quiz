import { useAuth } from '@/features/auth/AuthContext';
import { echoManager } from '@/lib/echoManager';
import { GameSessionParticipantJoinedDataSchema } from '@/schemas/App/Data/Events/GameSessionParticipantJoinedData';
import { GameSessionUpdatedDataSchema } from '@/schemas/App/Data/Events/GameSessionUpdatedData';
import { Schema } from 'effect';
import { useEffect, useRef, useState } from 'react';

export function useGameSessionChannel(
    sessionId: string | undefined,
    options?: { onSessionUpdated?: () => void },
) {
    const { authState } = useAuth();
    const [participantCount, setParticipantCount] = useState<number | null>(
        null,
    );
    const onSessionUpdatedRef = useRef(options?.onSessionUpdated);

    useEffect(() => {
        onSessionUpdatedRef.current = options?.onSessionUpdated;
    }, [options?.onSessionUpdated]);

    useEffect(() => {
        if (
            !sessionId ||
            !authState.hasFetchedUser ||
            !authState.isAuthenticated
        ) {
            return;
        }

        const channelName = `game-session.${sessionId}`;

        const onParticipantJoined = (data: unknown) => {
            const decoded = Schema.decodeUnknownEither(
                GameSessionParticipantJoinedDataSchema,
            )(data);
            if (decoded._tag === 'Right') {
                setParticipantCount(decoded.right.participant_count);
            }
        };

        const onSessionUpdated = (data: unknown) => {
            const decoded = Schema.decodeUnknownEither(
                GameSessionUpdatedDataSchema,
            )(data);
            if (decoded._tag === 'Right' && decoded.right.session_id === sessionId) {
                onSessionUpdatedRef.current?.();
            }
        };

        echoManager.subscribeNotifications(
            channelName,
            'GameSessionParticipantJoined',
            onParticipantJoined,
        );
        echoManager.subscribeNotifications(
            channelName,
            'GameSessionUpdated',
            onSessionUpdated,
        );

        return () => {
            echoManager.unsubscribeNotifications(
                channelName,
                'GameSessionParticipantJoined',
                onParticipantJoined,
            );
            echoManager.unsubscribeNotifications(
                channelName,
                'GameSessionUpdated',
                onSessionUpdated,
            );
        };
    }, [authState.hasFetchedUser, authState.isAuthenticated, sessionId]);

    return { participantCount };
}
