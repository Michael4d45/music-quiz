import { AuthContextState } from '@/features/auth/AuthContext';
import { devInfo, devLog, devWarn } from '@/lib/devLogging';
import { echoManager } from '@/lib/echoManager';
import { Schema } from 'effect';
import { useEffect, useState } from 'react';

export function useNotificationsChannel<A, R>(
    authState: AuthContextState,
    channelName: string,
    schema: Schema.Schema<A, R>,
    debugContext: string,
) {
    const [messages, setMessages] = useState<A[]>([]);

    useEffect(() => {
        if (
            !authState.hasFetchedUser ||
            !authState.isAuthenticated ||
            !channelName
        ) {
            return;
        }

        devInfo(
            `[${debugContext}][useNotificationsChannel] Subscribing to ${channelName}`,
        );
        const callback = (data: unknown) => {
            const result = Schema.decodeUnknownEither(schema)(data);
            if (result._tag === 'Right') {
                devLog(
                    `[${debugContext}][useNotificationsChannel] Decoded: `,
                    result.right,
                );
                setMessages((prev) => [...prev, result.right]);
            } else {
                devWarn(
                    `[${debugContext}][useNotificationsChannel] Failed to decode message:`,
                    result.left,
                );
            }
        };

        echoManager.subscribeNotifications(
            channelName,
            'TestRealtimeEvent',
            callback,
        );

        return () => {
            devInfo(
                `[${debugContext}][useNotificationsChannel] Unsubscribing from ${channelName}`,
            );
            echoManager.unsubscribeNotifications(
                channelName,
                'TestRealtimeEvent',
                callback,
            );
        };
    }, [authState.hasFetchedUser, authState.isAuthenticated, channelName]);

    const clearMessages = () => {
        setMessages([]);
    };

    return { messages, clearMessages };
}
