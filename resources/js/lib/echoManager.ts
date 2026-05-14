import { echo } from './echo';

interface PrivateNotificationSubscription {
    channel: any;
    eventListeners: Map<string, Set<(data: unknown) => void>>;
}

interface PublicNotificationSubscription {
    channel: any;
    refCount: number;
    callbacks: Set<(data: unknown) => void>;
}

interface PresenceSubscription {
    channel: any;
    refCount: number;
    hereCallbacks: Set<(members: any[]) => void>;
    joiningCallbacks: Set<(member: any) => void>;
    leavingCallbacks: Set<(member: any) => void>;
}

interface PresenceNotificationSubscription {
    channel: any;
    refCount: number;
    callbacks: Set<(data: unknown) => void>;
}

class EchoManager {
    private notificationSubscriptions = new Map<
        string,
        PrivateNotificationSubscription
    >();
    private publicNotificationSubscriptions = new Map<
        string,
        PublicNotificationSubscription
    >();
    private presenceSubscriptions = new Map<string, PresenceSubscription>();
    private presenceNotificationSubscriptions = new Map<
        string,
        PresenceNotificationSubscription
    >();

    subscribeNotifications(
        channelName: string,
        eventName: string,
        callback: (data: unknown) => void,
    ) {
        let sub = this.notificationSubscriptions.get(channelName);
        if (!sub) {
            sub = {
                channel: echo.private(channelName),
                eventListeners: new Map(),
            };
            this.notificationSubscriptions.set(channelName, sub);
        }

        let callbacks = sub.eventListeners.get(eventName);
        if (!callbacks) {
            callbacks = new Set();
            sub.eventListeners.set(eventName, callbacks);

            const subRef = sub;
            sub.channel.listen(`.${eventName}`, (data: unknown) => {
                subRef.eventListeners
                    .get(eventName)
                    ?.forEach((cb) => cb(data));
            });
        }

        callbacks.add(callback);
    }

    subscribePublicNotifications(
        channelName: string,
        eventName: string,
        callback: (data: unknown) => void,
    ) {
        let sub = this.publicNotificationSubscriptions.get(channelName);
        if (!sub) {
            sub = {
                channel: echo.channel(channelName),
                refCount: 0,
                callbacks: new Set(),
            };
            this.publicNotificationSubscriptions.set(channelName, sub);

            const currentSub = sub;
            sub.channel.listen(`.${eventName}`, (data: unknown) => {
                currentSub.callbacks.forEach((cb) => cb(data));
            });
        }
        sub.callbacks.add(callback);
        sub.refCount++;
    }

    unsubscribeNotifications(
        channelName: string,
        eventName: string,
        callback: (data: unknown) => void,
    ) {
        const sub = this.notificationSubscriptions.get(channelName);
        if (!sub) {
            return;
        }

        const callbacks = sub.eventListeners.get(eventName);
        if (!callbacks) {
            return;
        }

        callbacks.delete(callback);

        if (callbacks.size === 0) {
            sub.channel.stopListening(`.${eventName}`);
            sub.eventListeners.delete(eventName);
        }

        if (sub.eventListeners.size === 0) {
            echo.leave(channelName);
            this.notificationSubscriptions.delete(channelName);
        }
    }

    unsubscribePublicNotifications(
        channelName: string,
        eventName: string,
        callback: (data: unknown) => void,
    ) {
        const sub = this.publicNotificationSubscriptions.get(channelName);
        if (!sub) return;
        sub.callbacks.delete(callback);
        sub.refCount--;
        if (sub.refCount === 0) {
            sub.channel.stopListening(`.${eventName}`);
            echo.leave(channelName);
            this.publicNotificationSubscriptions.delete(channelName);
        }
    }

    subscribePresence(
        channelName: string,
        hereCallback: (members: any[]) => void,
        joiningCallback: (member: any) => void,
        leavingCallback: (member: any) => void,
    ) {
        let sub = this.presenceSubscriptions.get(channelName);
        if (!sub) {
            sub = {
                channel: echo.join(channelName),
                refCount: 0,
                hereCallbacks: new Set(),
                joiningCallbacks: new Set(),
                leavingCallbacks: new Set(),
            };
            this.presenceSubscriptions.set(channelName, sub);

            const currentSub = sub;
            sub.channel
                .here((members: any[]) => {
                    currentSub.hereCallbacks.forEach(
                        (cb: (members: any[]) => void) => cb(members),
                    );
                })
                .joining((member: any) => {
                    currentSub.joiningCallbacks.forEach(
                        (cb: (member: any) => void) => cb(member),
                    );
                })
                .leaving((member: any) => {
                    currentSub.leavingCallbacks.forEach(
                        (cb: (member: any) => void) => cb(member),
                    );
                });
        }
        sub.hereCallbacks.add(hereCallback);
        sub.joiningCallbacks.add(joiningCallback);
        sub.leavingCallbacks.add(leavingCallback);
        sub.refCount++;
    }

    unsubscribePresence(
        channelName: string,
        hereCallback: (members: any[]) => void,
        joiningCallback: (member: any) => void,
        leavingCallback: (member: any) => void,
    ) {
        const sub = this.presenceSubscriptions.get(channelName);
        if (!sub) return;
        sub.hereCallbacks.delete(hereCallback);
        sub.joiningCallbacks.delete(joiningCallback);
        sub.leavingCallbacks.delete(leavingCallback);
        sub.refCount--;
        if (sub.refCount === 0) {
            echo.leave(channelName);
            this.presenceSubscriptions.delete(channelName);
        }
    }

    subscribePresenceNotifications(
        channelName: string,
        eventName: string,
        callback: (data: unknown) => void,
    ) {
        let sub = this.presenceNotificationSubscriptions.get(channelName);
        if (!sub) {
            sub = {
                channel: echo.join(channelName),
                refCount: 0,
                callbacks: new Set(),
            };
            this.presenceNotificationSubscriptions.set(channelName, sub);

            // Listen to the event
            const currentSub = sub;
            sub.channel.listen(`.${eventName}`, (data: unknown) => {
                currentSub.callbacks.forEach((cb) => cb(data));
            });
        }
        sub.callbacks.add(callback);
        sub.refCount++;
    }

    unsubscribePresenceNotifications(
        channelName: string,
        eventName: string,
        callback: (data: unknown) => void,
    ) {
        const sub = this.presenceNotificationSubscriptions.get(channelName);
        if (!sub) return;
        sub.callbacks.delete(callback);
        sub.refCount--;
        if (sub.refCount === 0) {
            sub.channel.stopListening(`.${eventName}`);
            echo.leave(channelName);
            this.presenceNotificationSubscriptions.delete(channelName);
        }
    }
}

export const echoManager = new EchoManager();
