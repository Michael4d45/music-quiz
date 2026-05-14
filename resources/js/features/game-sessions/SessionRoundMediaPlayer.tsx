import { Button } from '@/components/ui/Button';
import { syncGameSessionRoundMediaPlayback } from '@/features/game-sessions/api';
import { gameSessionRoundAudioUrl } from '@/features/game-sessions/gameSessionRoundAudioUrl';
import { cn } from '@/lib/utils';
import { useEffect, useId, useRef, useState } from 'react';

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

function formatClock(seconds: number): string {
    let s = seconds;
    if (!Number.isFinite(s) || s < 0) {
        s = 0;
    }
    const m = Math.floor(s / 60);
    const r = Math.floor(s % 60);
    return `${m}:${r.toString().padStart(2, '0')}`;
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
    const seekDebounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(
        null,
    );
    const pushHostPlaybackRef = useRef<
        (playing: boolean, currentTimeSeconds: number) => Promise<void>
    >(async () => {});

    const [followerAudioGateOpen, setFollowerAudioGateOpen] = useState(false);
    const [trackDurationSeconds, setTrackDurationSeconds] = useState(0);
    const [followerPlaybackTick, setFollowerPlaybackTick] = useState(0);
    const followerPositionSliderId = useId();

    const audioSrc = hasAudio
        ? gameSessionRoundAudioUrl(sessionId, roundId)
        : null;

    useEffect(() => {
        pushHostPlaybackRef.current = async (
            playing: boolean,
            currentTimeSeconds: number,
        ) => {
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
    }, [variant, sessionId, roundId, mediaStartSeconds, mediaEndSeconds]);

    const enableFollowerRoundAudio = async (): Promise<void> => {
        if (variant !== 'follower') {
            return;
        }
        const audio = audioRef.current;
        if (!audio) {
            lastRemoteSeqRef.current = -1;
            setFollowerAudioGateOpen(true);
            return;
        }
        if (remotePlayback?.round_id === roundId) {
            audio.currentTime = clampTime(
                remotePlayback.current_time_seconds,
                mediaStartSeconds,
                mediaEndSeconds,
            );
        }
        const hostPlaying =
            remotePlayback?.round_id === roundId && remotePlayback.playing;
        if (hostPlaying) {
            try {
                await audio.play();
            } catch {
                //
            }
        } else {
            const previousMuted = audio.muted;
            audio.muted = true;
            try {
                await audio.play();
                audio.pause();
            } catch {
                //
            } finally {
                audio.muted = previousMuted;
            }
        }
        lastRemoteSeqRef.current = -1;
        setFollowerAudioGateOpen(true);
    };

    useEffect(() => {
        lastRemoteSeqRef.current = -1;
        audioRef.current?.pause();
    }, [roundId]);

    useEffect(() => {
        const el = audioRef.current;
        if (!el || !audioSrc) {
            return;
        }

        const onLoaded = () => {
            const d = el.duration;
            if (Number.isFinite(d) && d > 0) {
                setTrackDurationSeconds(d);
            }
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
                    void pushHostPlaybackRef.current(false, el.currentTime);
                }
            }
        };

        el.addEventListener('timeupdate', onTimeUpdate);
        return () => {
            el.removeEventListener('timeupdate', onTimeUpdate);
        };
    }, [audioSrc, mediaEndSeconds, mediaStartSeconds, variant]);

    useEffect(() => {
        if (variant !== 'host' || !audioRef.current || !audioSrc) {
            return;
        }

        const audio = audioRef.current;

        const onPlay = () => {
            void pushHostPlaybackRef.current(true, audio.currentTime);
        };
        const onPause = () => {
            void pushHostPlaybackRef.current(false, audio.currentTime);
        };
        const onSeeked = () => {
            if (seekDebounceTimerRef.current != null) {
                clearTimeout(seekDebounceTimerRef.current);
            }
            seekDebounceTimerRef.current = setTimeout(() => {
                seekDebounceTimerRef.current = null;
                void pushHostPlaybackRef.current(
                    !audio.paused,
                    audio.currentTime,
                );
            }, 320);
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
            void pushHostPlaybackRef.current(true, audio.currentTime);
        };

        audio.addEventListener('play', onPlay);
        audio.addEventListener('pause', onPause);
        audio.addEventListener('seeked', onSeeked);
        audio.addEventListener('timeupdate', onTimeUpdate);

        return () => {
            if (seekDebounceTimerRef.current != null) {
                clearTimeout(seekDebounceTimerRef.current);
                seekDebounceTimerRef.current = null;
            }
            audio.removeEventListener('play', onPlay);
            audio.removeEventListener('pause', onPause);
            audio.removeEventListener('seeked', onSeeked);
            audio.removeEventListener('timeupdate', onTimeUpdate);
        };
    }, [variant, audioSrc]);

    useEffect(() => {
        if (variant !== 'follower' || !audioRef.current || !audioSrc) {
            return;
        }
        if (!followerAudioGateOpen) {
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
            const playStateMatches = shouldPlay === !audio.paused;
            const driftSeconds = Math.abs(audio.currentTime - t);
            if (playStateMatches && driftSeconds < 0.75) {
                return;
            }

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
        followerAudioGateOpen,
        remotePlayback,
        roundId,
        audioSrc,
        mediaStartSeconds,
        mediaEndSeconds,
    ]);

    useEffect(() => {
        if (variant !== 'follower' || !followerAudioGateOpen || !audioSrc) {
            return;
        }
        const el = audioRef.current;
        if (!el) {
            return;
        }
        const bump = () => {
            if (!el.paused) {
                setFollowerPlaybackTick((n) => n + 1);
            }
        };
        el.addEventListener('timeupdate', bump);
        el.addEventListener('seeked', bump);
        return () => {
            el.removeEventListener('timeupdate', bump);
            el.removeEventListener('seeked', bump);
        };
    }, [variant, followerAudioGateOpen, audioSrc, roundId]);

    if (!audioSrc) {
        return null;
    }

    void followerPlaybackTick;

    const remoteFollowerSeconds =
        variant === 'follower' &&
        remotePlayback != null &&
        remotePlayback.round_id === roundId
            ? clampTime(
                  remotePlayback.current_time_seconds,
                  mediaStartSeconds,
                  mediaEndSeconds,
              )
            : 0;

    const audioEl = audioRef.current;
    const followerLiveFromElement =
        variant === 'follower' &&
        followerAudioGateOpen &&
        remotePlayback?.round_id === roundId &&
        remotePlayback.playing &&
        audioEl !== null &&
        !audioEl.paused;

    const followerSliderSeconds = followerLiveFromElement
        ? clampTime(audioEl.currentTime, mediaStartSeconds, mediaEndSeconds)
        : remoteFollowerSeconds;

    let followerSliderMax = trackDurationSeconds;
    if (!Number.isFinite(followerSliderMax) || followerSliderMax <= 0) {
        followerSliderMax = 1;
    }
    if (mediaEndSeconds != null) {
        followerSliderMax = Math.min(followerSliderMax, mediaEndSeconds);
    }
    followerSliderMax = Math.max(followerSliderMax, 0.01);

    const followerSliderValue = Math.min(
        Math.max(followerSliderSeconds, 0),
        followerSliderMax,
    );

    return (
        <div
            className={cn(
                'mt-3 flex flex-col gap-3 rounded-md border border-transparent p-3 dark:border-white/10',
                variant === 'follower' && 'bg-black/5 dark:bg-white/5',
                variant === 'host' && 'bg-primary/5 dark:bg-primary/10',
                variant === 'recap' && 'bg-black/5 dark:bg-white/5',
            )}
        >
            {variant === 'follower' ? (
                <div className="border-border flex flex-wrap items-start gap-3 border-b pb-3 dark:border-white/10">
                    {!followerAudioGateOpen ? (
                        <>
                            <Button
                                type="button"
                                variant="secondary"
                                className="shrink-0"
                                onClick={() => void enableFollowerRoundAudio()}
                            >
                                Allow audio
                            </Button>
                            <p className="text-muted min-w-0 flex-1 text-sm leading-snug">
                                Tap once so your browser can play
                                host-controlled sound. You will not hear audio
                                until the host plays this round.
                            </p>
                        </>
                    ) : (
                        <p className="text-muted text-sm leading-snug">
                            Audio is allowed. Playback starts only when the host
                            plays the round.
                        </p>
                    )}
                </div>
            ) : null}
            <div className="flex flex-wrap items-center justify-between gap-2">
                <span className="text-muted text-xs font-medium tracking-wide uppercase">
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
            {variant === 'follower' ? (
                <div className="flex flex-col gap-1">
                    <label
                        className="sr-only"
                        htmlFor={followerPositionSliderId}
                    >
                        {ariaLabel} position
                    </label>
                    <input
                        id={followerPositionSliderId}
                        type="range"
                        min={0}
                        max={followerSliderMax}
                        step={0.05}
                        value={followerSliderValue}
                        tabIndex={-1}
                        className="accent-primary pointer-events-none h-2 w-full"
                        aria-valuemin={0}
                        aria-valuemax={followerSliderMax}
                        aria-valuenow={followerSliderValue}
                        aria-valuetext={`${formatClock(followerSliderValue)} of ${formatClock(followerSliderMax)}`}
                    />
                    <div className="text-muted flex justify-between text-xs tabular-nums">
                        <span>{formatClock(followerSliderValue)}</span>
                        <span>{formatClock(followerSliderMax)}</span>
                    </div>
                </div>
            ) : null}
            <audio
                ref={audioRef}
                controls={variant !== 'follower'}
                preload="metadata"
                src={audioSrc}
                className={cn(
                    variant === 'follower' && 'sr-only',
                    variant !== 'follower' && 'h-9 w-full max-w-xl min-w-48',
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
