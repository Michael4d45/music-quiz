import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { useOfflineBlock } from '@/hooks/useOfflineBlock';
import type { GameSessionsLobbyResponseData } from '@/schemas/App/Data/Models/GameSessionsLobbyResponseData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useNavigate } from 'react-router-dom';
import { fetchGameSessionsLobby, joinGameSession } from './api';

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
    return { sessions: [] };
}

export function GameSessionsLobbyPage() {
    const { sessions } = useLoaderData<GameSessionsLobbyResponseData>();
    const { isBlocked, blockReason } = useOfflineBlock();
    const navigate = useNavigate();
    const [joinCode, setJoinCode] = useState('');

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
        const result = await joinGameSession(normalized);
        if (result._tag === 'Success') {
            toast.success('Joined');
            navigate(`/game-sessions/room/${normalized}`);
        } else {
            toast.error('Could not join this room');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">Game lobby</h1>
                <ButtonLink to="/my/game-sessions" variant="secondary">
                    Host a session
                </ButtonLink>
            </div>

            <p className="text-muted mb-6 max-w-2xl">
                Public games that have not started yet. Join with a room code,
                or open a session you host from My game sessions.
            </p>

            <div className="bg-card mb-6 flex flex-col gap-3 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10 sm:flex-row sm:items-end">
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

            <div className="mb-6">
                <Button
                    onClick={() => window.location.reload()}
                    disabled={isBlocked}
                >
                    {isBlocked ? 'Refresh (Offline)' : 'Refresh list'}
                </Button>
            </div>

            {sessions.length > 0 ? (
                <ul className="grid grid-cols-1 gap-4 md:grid-cols-2" role="list">
                    {sessions.map((session) => {
                        const isFull =
                            session.participant_count >= session.max_players;
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
                                                    <span className="ml-2 text-sm text-amber-600 dark:text-amber-400">
                                                        Full
                                                    </span>
                                                ) : null}
                                            </dd>
                                        </div>
                                    </dl>
                                    <Button
                                        type="button"
                                        disabled={isBlocked || isFull}
                                        onClick={() =>
                                            void goToRoomAfterJoin(
                                                session.room_code,
                                            )
                                        }
                                    >
                                        {isFull ? 'Full' : 'Join this game'}
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
