import { syncGameSessionRoundMediaPlayback } from '@/features/game-sessions/api';
import { gameSessionRoundAudioUrl } from '@/features/game-sessions/gameSessionRoundAudioUrl';
import { cn } from '@/lib/utils';
import { useCallback, useEffect, useRef } from 'react';

export type SessionRoundMediaVariant = 'host' | 'follower' | 'recap';

export interface SessionRoundRemotePlayback {
    readonly round_id: string;
    readonly playing: boolean;
    readonly current_time_seconds: number;
    readonly server_seq: number;
}

function clampTime(
    seconds: number,
    mediaStartSeconds: number | null,
    mediaEndSeconds: number | null,
): number {
    let t = seconds;
    if (mediaStartSeconds != null && t < mediaStartSeconds) {
        t = mediaStartSeconds;
    }
    if (mediaEndSeconds != null && t > mediaEndSeconds) {
        t = mediaEndSeconds;
    }
    return t;
}

export interface SessionRoundMediaPlayerProps {
    readonly sessionId: string;
    readonly roundId: string;
    readonly variant: SessionRoundMediaVariant;
    readonly mediaStartSeconds: number | null;
    readonly mediaEndSeconds: number | null;
    readonly ariaLabel: string;
    /** When null, nothing is rendered (no uploaded audio for this question). */
    readonly hasAudio: boolean;
    readonly remotePlayback: SessionRoundRemotePlayback | null;
}

export function SessionRoundMediaPlayer({
    sessionId,
    roundId,
    variant,
    mediaStartSeconds,
    mediaEndSeconds,
    ariaLabel,
    hasAudio,
    remotePlayback,
}: SessionRoundMediaPlayerProps) {
    const audioRef = useRef<HTMLAudioElement | null>(null);
    const lastRemoteSeqRef = useRef<number>(-1);
    const lastHostThrottleRef = useRef<number>(0);

    const audioSrc = hasAudio
        ? gameSessionRoundAudioUrl(sessionId, roundId)
        : null;

    const pushHostPlayback = async (playing: boolean, currentTimeSeconds: number) => {
            if (variant !== 'host') {
                return;
            }
            const t = clampTime(
                currentTimeSeconds,
                mediaStartSeconds,
                mediaEndSeconds,
            );
            await syncGameSessionRoundMediaPlayback(sessionId, roundId, {
                playing,
                current_time_seconds: t,
            });
        };

    useEffect(() => {
        lastRemoteSeqRef.current = -1;
        const el = audioRef.current;
        if (el) {
            el.pause();
            el.removeAttribute('src');
            el.load();
        }
    }, [roundId, audioSrc]);

    useEffect(() => {
        const el = audioRef.current;
        if (!el || !audioSrc) {
            return;
        }

        const onLoaded = () => {
            if (mediaStartSeconds != null) {
                el.currentTime = clampTime(
                    mediaStartSeconds,
                    mediaStartSeconds,
                    mediaEndSeconds,
                );
            }
        };

        el.addEventListener('loadedmetadata', onLoaded);
        return () => {
            el.removeEventListener('loadedmetadata', onLoaded);
        };
    }, [audioSrc, mediaStartSeconds, mediaEndSeconds, roundId]);

    useEffect(() => {
        const el = audioRef.current;
        if (!el || !audioSrc) {
            return;
        }

        const onTimeUpdate = () => {
            if (mediaEndSeconds == null) {
                return;
            }
            if (el.currentTime >= mediaEndSeconds) {
                el.pause();
                el.currentTime = clampTime(
                    mediaEndSeconds,
                    mediaStartSeconds,
                    mediaEndSeconds,
                );
                if (variant === 'host') {
                    void pushHostPlayback(false, el.currentTime);
                }
            }
        };

        el.addEventListener('timeupdate', onTimeUpdate);
        return () => {
            el.removeEventListener('timeupdate', onTimeUpdate);
        };
    }, [
        audioSrc,
        mediaEndSeconds,
        mediaStartSeconds,
        variant,
        pushHostPlayback,
    ]);

    useEffect(() => {
        if (variant !== 'host' || !audioRef.current || !audioSrc) {
            return;
        }

        const audio = audioRef.current;

        const onPlay = () => {
            void pushHostPlayback(true, audio.currentTime);
        };
        const onPause = () => {
            void pushHostPlayback(false, audio.currentTime);
        };
        const onSeeked = () => {
            void pushHostPlayback(!audio.paused, audio.currentTime);
        };

        const onTimeUpdate = () => {
            if (audio.paused) {
                return;
            }
            const now = performance.now();
            if (now - lastHostThrottleRef.current < 650) {
                return;
            }
            lastHostThrottleRef.current = now;
            void pushHostPlayback(true, audio.currentTime);
        };

        audio.addEventListener('play', onPlay);
        audio.addEventListener('pause', onPause);
        audio.addEventListener('seeked', onSeeked);
        audio.addEventListener('timeupdate', onTimeUpdate);

        return () => {
            audio.removeEventListener('play', onPlay);
            audio.removeEventListener('pause', onPause);
            audio.removeEventListener('seeked', onSeeked);
            audio.removeEventListener('timeupdate', onTimeUpdate);
        };
    }, [variant, audioSrc, pushHostPlayback]);

    useEffect(() => {
        if (variant !== 'follower' || !audioRef.current || !audioSrc) {
            return;
        }
        if (!remotePlayback || remotePlayback.round_id !== roundId) {
            return;
        }
        if (remotePlayback.server_seq <= lastRemoteSeqRef.current) {
            return;
        }
        lastRemoteSeqRef.current = remotePlayback.server_seq;

        const audio = audioRef.current;
        const t = clampTime(
            remotePlayback.current_time_seconds,
            mediaStartSeconds,
            mediaEndSeconds,
        );
        const shouldPlay = remotePlayback.playing;

        const apply = () => {
            audio.pause();
            audio.currentTime = t;
            if (shouldPlay) {
                void audio.play().catch(() => {});
            }
        };

        if (audio.readyState >= 1) {
            apply();
        } else {
            const once = () => {
                audio.removeEventListener('loadedmetadata', once);
                apply();
            };
            audio.addEventListener('loadedmetadata', once);
        }
    }, [
        variant,
        remotePlayback,
        roundId,
        audioSrc,
        mediaStartSeconds,
        mediaEndSeconds,
    ]);

    if (!audioSrc) {
        return null;
    }

    return (
        <div
            className={cn(
                'mt-3 flex flex-col gap-2 rounded-md border border-transparent p-3 dark:border-white/10',
                variant === 'follower' && 'bg-black/5 dark:bg-white/5',
                variant === 'host' && 'bg-primary/5 dark:bg-primary/10',
                variant === 'recap' && 'bg-black/5 dark:bg-white/5',
            )}
        >
            <div className="flex flex-wrap items-center justify-between gap-2">
                <span className="text-muted text-xs font-medium uppercase tracking-wide">
                    {variant === 'recap' ? 'Listen' : 'Round audio'}
                </span>
                {variant === 'follower' ? (
                    <span className="text-muted max-w-[16rem] text-right text-xs">
                        The host controls playback for everyone.
                    </span>
                ) : variant === 'host' ? (
                    <span className="text-muted max-w-[16rem] text-right text-xs">
                        Everyone hears what you play here.
                    </span>
                ) : null}
            </div>
            <audio
                ref={audioRef}
                controls={variant !== 'follower'}
                preload="metadata"
                src={audioSrc}
                className={cn(
                    'h-9 w-full min-w-[12rem] max-w-xl',
                    variant === 'follower' && 'pointer-events-none opacity-90',
                )}
                aria-label={ariaLabel}
            >
                <a className="text-primary text-xs underline" href={audioSrc}>
                    Open audio
                </a>
            </audio>
        </div>
    );
}
