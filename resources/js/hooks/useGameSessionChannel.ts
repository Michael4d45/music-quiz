import { useAuth } from '@/features/auth/AuthContext';
import { echoManager } from '@/lib/echoManager';
import { GameSessionParticipantJoinedDataSchema } from '@/schemas/App/Data/Events/GameSessionParticipantJoinedData';
import type { GameSessionRoundMediaPlaybackData } from '@/schemas/App/Data/Events/GameSessionRoundMediaPlaybackData';
import { GameSessionRoundMediaPlaybackDataSchema } from '@/schemas/App/Data/Events/GameSessionRoundMediaPlaybackData';
import { GameSessionUpdatedDataSchema } from '@/schemas/App/Data/Events/GameSessionUpdatedData';
import { Schema } from 'effect';
import { useEffect, useRef, useState } from 'react';

export function useGameSessionChannel(
    sessionId: string | undefined,
    options?: {
        onSessionUpdated?: () => void;
        onRoundMediaPlayback?: (
            data: GameSessionRoundMediaPlaybackData,
        ) => void;
        /** When false, the client will not subscribe to host media sync events. */
        subscribeRoundMediaPlayback?: boolean;
    },
) {
    const { authState } = useAuth();
    const [participantCount, setParticipantCount] = useState<number | null>(
        null,
    );
    const onSessionUpdatedRef = useRef(options?.onSessionUpdated);
    const onRoundMediaPlaybackRef = useRef(options?.onRoundMediaPlayback);

    useEffect(() => {
        onSessionUpdatedRef.current = options?.onSessionUpdated;
    }, [options?.onSessionUpdated]);

    useEffect(() => {
        onRoundMediaPlaybackRef.current = options?.onRoundMediaPlayback;
    }, [options?.onRoundMediaPlayback]);

    const subscribeRoundMediaPlayback =
        options?.subscribeRoundMediaPlayback ?? true;

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
                onSessionUpdatedRef.current?.();
            }
        };

        const onSessionUpdated = (data: unknown) => {
            const decoded = Schema.decodeUnknownEither(
                GameSessionUpdatedDataSchema,
            )(data);
            if (
                decoded._tag === 'Right' &&
                decoded.right.session_id === sessionId
            ) {
                setParticipantCount(null);
                onSessionUpdatedRef.current?.();
            }
        };

        const onRoundMediaPlayback = (data: unknown) => {
            const decoded = Schema.decodeUnknownEither(
                GameSessionRoundMediaPlaybackDataSchema,
            )(data);
            if (
                decoded._tag === 'Right' &&
                decoded.right.session_id === sessionId
            ) {
                onRoundMediaPlaybackRef.current?.(decoded.right);
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

        if (subscribeRoundMediaPlayback) {
            echoManager.subscribeNotifications(
                channelName,
                'GameSessionRoundMediaPlayback',
                onRoundMediaPlayback,
            );
        }

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
            if (subscribeRoundMediaPlayback) {
                echoManager.unsubscribeNotifications(
                    channelName,
                    'GameSessionRoundMediaPlayback',
                    onRoundMediaPlayback,
                );
            }
        };
    }, [
        authState.hasFetchedUser,
        authState.isAuthenticated,
        sessionId,
        subscribeRoundMediaPlayback,
    ]);

    return { participantCount };
}
