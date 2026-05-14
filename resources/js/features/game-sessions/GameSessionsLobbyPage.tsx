import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import { useAuth } from '@/features/auth/AuthContext';
import { useOfflineBlock } from '@/hooks/useOfflineBlock';
import { apiFailureMessage } from '@/lib/apiCore';
import { modal } from '@/lib/modal';
import type { GameSessionLobbyCurrentSessionData } from '@/schemas/App/Data/Models/GameSessionLobbyCurrentSessionData';
import type { GameSessionsLobbyResponseData } from '@/schemas/App/Data/Models/GameSessionsLobbyResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useNavigate } from 'react-router-dom';
import { fetchGameSessionsLobby, joinGameSession } from './api';
import { gameSessionStatusLabel } from './gameSessionStatusLabel';

export async function gameSessionsLobbyLoader(): Promise<GameSessionsLobbyResponseData> {
    const result = await fetchGameSessionsLobby();
    if (result._tag === 'Success') {
        return result.data;
    }
    const errorMessage =
        result._tag === 'ParseError' || result._tag === 'FatalError'
            ? result.message
            : 'Could not load joinable games';
    console.error('Failed to load game session lobby:', errorMessage);
    return { sessions: [], current_session: null };
}

export function GameSessionsLobbyPage() {
    const { sessions, current_session } =
        useLoaderData<GameSessionsLobbyResponseData>();
    const { user } = useAuth();
    const { isBlocked, blockReason } = useOfflineBlock();
    const navigate = useNavigate();
    const [joinCode, setJoinCode] = useState('');
    const [joinDisplayName, setJoinDisplayName] = useState('');

    const returnToRoom = (roomCode: string) => {
        navigate(`/game-sessions/room/${roomCode.trim().toUpperCase()}`);
    };

    const copyRoomCode = async (code: string) => {
        if (isBlocked) {
            toast.error(blockReason || 'Cannot copy while offline');
            return;
        }
        try {
            await navigator.clipboard.writeText(code);
            toast.success('Room code copied');
        } catch {
            toast.error('Could not copy room code');
        }
    };

    const goToRoomAfterJoin = async (code: string) => {
        if (isBlocked) {
            toast.error(blockReason || 'Cannot join while offline');
            return;
        }
        const normalized = code.trim().toUpperCase();
        if (normalized.length !== 6) {
            toast.error('Room code must be 6 characters');
            return;
        }

        if (
            current_session !== null &&
            normalized !== current_session.room_code.toUpperCase()
        ) {
            if (user?.is_guest) {
                toast.error(
                    `You are already in room ${current_session.room_code}. Leave that game before joining another.`,
                );
                return;
            }
            let confirmed = false;
            try {
                confirmed = await modal.confirm({
                    title: 'Join another game?',
                    message: `You already have an active game (${current_session.room_code}). Join ${normalized} anyway?`,
                    confirmText: 'Join anyway',
                    cancelText: 'Cancel',
                });
            } catch {
                confirmed = false;
            }
            if (!confirmed) {
                return;
            }
        }

        const result = await joinGameSession(normalized, {
            display_name: joinDisplayName,
        });
        if (result._tag === 'Success') {
            toast.success('Joined');
            navigate(`/game-sessions/room/${normalized}`);
        } else {
            toast.error(apiFailureMessage(result, 'Could not join this room'));
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <h1 className="mb-6 text-2xl font-bold">Game lobby</h1>

            {current_session ? (
                <ActiveSessionBanner
                    session={current_session}
                    isOffline={isBlocked}
                    currentUserId={user?.id ?? null}
                    onReturn={() => returnToRoom(current_session.room_code)}
                />
            ) : null}

            <PageIntroExpandable
                summary="Join listed games that have not started, or enter a six-character room code."
                moreLabel="More about the public lobby"
            >
                <p>
                    Public games that have not started yet. Join with a room
                    code, or open a session you host from My game sessions. If
                    you already have an active game, it is highlighted above;
                    you can return even when a listed room shows as full. Guest
                    accounts can only join one active game at a time—leave your
                    current room before joining another.
                </p>
            </PageIntroExpandable>

            <div className="bg-card mb-6 flex flex-col gap-4 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div className="grow">
                        <label
                            htmlFor="join-code"
                            className="text-muted mb-1 block text-sm font-medium"
                        >
                            Join by room code
                        </label>
                        <input
                            id="join-code"
                            maxLength={6}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 font-mono text-lg tracking-widest uppercase"
                            value={joinCode}
                            onChange={(e) =>
                                setJoinCode(e.target.value.toUpperCase())
                            }
                            placeholder="ABC123"
                        />
                    </div>
                    <Button
                        type="button"
                        disabled={isBlocked}
                        onClick={() => void goToRoomAfterJoin(joinCode)}
                    >
                        Join
                    </Button>
                </div>
                <div>
                    <label
                        htmlFor="join-display-name"
                        className="text-muted mb-1 block text-sm font-medium"
                    >
                        Name in this room (optional)
                    </label>
                    <input
                        id="join-display-name"
                        type="text"
                        maxLength={64}
                        autoComplete="nickname"
                        className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                        value={joinDisplayName}
                        onChange={(e) => setJoinDisplayName(e.target.value)}
                        placeholder="Shown to other players in this session only"
                    />
                </div>
            </div>

            <div className="mb-6">
                <Button
                    onClick={() => window.location.reload()}
                    disabled={isBlocked}
                >
                    {isBlocked ? 'Refresh (Offline)' : 'Refresh list'}
                </Button>
            </div>

            {sessions.length > 0 ? (
                <ul
                    className="grid grid-cols-1 gap-4 md:grid-cols-2"
                    role="list"
                >
                    {sessions.map((session) => {
                        const isFull =
                            session.participant_count >= session.max_players;
                        const isUsersActiveListedGame =
                            current_session !== null &&
                            current_session.id === session.id;
                        const joinDisabled =
                            isBlocked || (isFull && !isUsersActiveListedGame);
                        const primaryLabel = isUsersActiveListedGame
                            ? 'Return to your game'
                            : isFull
                              ? 'At capacity'
                              : 'Join this game';
                        const joinAriaLabel = isUsersActiveListedGame
                            ? `Return to your game in room ${session.room_code}`
                            : isFull
                              ? `Room ${session.room_code} is at capacity`
                              : `Join room ${session.room_code}`;

                        return (
                            <li key={session.id}>
                                <div className="bg-card flex flex-col gap-3 rounded-lg border border-transparent p-5 shadow-md dark:border-white/10">
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <p className="text-muted text-sm">
                                                Room code
                                            </p>
                                            <p className="font-mono text-xl font-semibold tracking-wide">
                                                {session.room_code}
                                            </p>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            className="shrink-0"
                                            disabled={isBlocked}
                                            aria-label={`Copy room code ${session.room_code}`}
                                            onClick={() =>
                                                void copyRoomCode(
                                                    session.room_code,
                                                )
                                            }
                                        >
                                            Copy
                                        </Button>
                                    </div>
                                    <dl className="grid gap-2 text-sm">
                                        <div className="flex justify-between gap-2">
                                            <dt className="text-muted">Host</dt>
                                            <dd className="text-right font-medium">
                                                {session.host_display_name}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between gap-2">
                                            <dt className="text-muted">Mode</dt>
                                            <dd className="text-right font-medium">
                                                {session.quiz_mode_name}
                                            </dd>
                                        </div>
                                        {session.playlist_name ? (
                                            <div className="flex justify-between gap-2">
                                                <dt className="text-muted">
                                                    Playlist
                                                </dt>
                                                <dd className="text-right font-medium">
                                                    {session.playlist_name}
                                                </dd>
                                            </div>
                                        ) : null}
                                        <div className="flex justify-between gap-2">
                                            <dt className="text-muted">
                                                Players
                                            </dt>
                                            <dd className="text-right font-medium">
                                                {session.participant_count} /{' '}
                                                {session.max_players}
                                                {isFull ? (
                                                    <span
                                                        className="ml-2 text-sm text-amber-600 dark:text-amber-400"
                                                        aria-hidden="true"
                                                    >
                                                        At capacity
                                                    </span>
                                                ) : null}
                                            </dd>
                                        </div>
                                    </dl>
                                    <Button
                                        type="button"
                                        disabled={joinDisabled}
                                        aria-label={joinAriaLabel}
                                        onClick={() =>
                                            isUsersActiveListedGame
                                                ? returnToRoom(
                                                      session.room_code,
                                                  )
                                                : void goToRoomAfterJoin(
                                                      session.room_code,
                                                  )
                                        }
                                    >
                                        {primaryLabel}
                                    </Button>
                                </div>
                            </li>
                        );
                    })}
                </ul>
            ) : (
                <div className="bg-card rounded-lg p-8 text-center shadow-md">
                    <p className="text-secondary mb-2">
                        No joinable public games right now.
                    </p>
                    <p className="text-muted text-sm">
                        {isBlocked
                            ? 'Go online to refresh the list.'
                            : 'Ask a host to mark their session as public in the lobby, or check back soon.'}
                    </p>
                </div>
            )}
        </div>
    );
}

interface ActiveSessionBannerProps {
    readonly session: GameSessionLobbyCurrentSessionData;
    readonly isOffline: boolean;
    readonly currentUserId: string | null;
    readonly onReturn: () => void;
}

function ActiveSessionBanner({
    session,
    isOffline,
    currentUserId,
    onReturn,
}: ActiveSessionBannerProps) {
    const visibilityLine = session.is_public
        ? 'This room is listed in the public lobby.'
        : 'This room is not listed here (private or not in lobby).';

    const roleLine =
        currentUserId !== null && currentUserId === session.host_id
            ? 'You are the host of this room.'
            : 'You are a player in this room.';

    return (
        <div className="border-primary/30 bg-card dark:border-primary/40 mb-6 rounded-lg border-2 border-dashed p-4 shadow-md">
            <h2 className="text-lg font-semibold">Your active game</h2>
            <p className="text-muted mt-1 text-sm">
                Room{' '}
                <span className="text-foreground font-mono font-semibold tracking-wide">
                    {session.room_code}
                </span>{' '}
                · {gameSessionStatusLabel(session.status)} · Host{' '}
                {session.host_display_name}
            </p>
            <p className="text-foreground mt-2 text-sm font-medium">
                {roleLine}
            </p>
            <p className="text-muted mt-1 text-xs">{visibilityLine}</p>
            <p className="text-muted mt-1 text-xs">
                Mode {session.quiz_mode_name}
                {session.playlist_name
                    ? ` · Playlist ${session.playlist_name}`
                    : ''}{' '}
                · Players {session.participant_count} / {session.max_players}
            </p>
            <div className="mt-3">
                <Button
                    type="button"
                    disabled={isOffline}
                    onClick={() => onReturn()}
                >
                    Return to room
                </Button>
            </div>
        </div>
    );
}
