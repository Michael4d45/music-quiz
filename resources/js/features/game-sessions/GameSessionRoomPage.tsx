import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { useAuth } from '@/features/auth/AuthContext';
import { isRegisteredUser } from '@/features/auth/authSession';
import { leaveGameSession } from '@/features/game-sessions/api';
import { useGameSessionChannel } from '@/hooks/useGameSessionChannel';
import type { GameSessionData } from '@/schemas/App/Data/Models/GameSessionData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { useLoaderData, useNavigate, useParams, useRevalidator } from 'react-router-dom';

export { gameSessionRoomLoader } from '@/features/game-sessions/gameSessionRoomLoader';

export function GameSessionRoomPage() {
    const { user } = useAuth();
    const session = useLoaderData<GameSessionData>();
    const params = useParams();
    const navigate = useNavigate();
    const revalidator = useRevalidator();
    const roomCode = params.roomCode ?? session.room_code;
    const { participantCount } = useGameSessionChannel(session.id);

    const displayCount = (() => {
        if (participantCount !== null) {
            return participantCount;
        }
        return session.participants?.length ?? 0;
    })();

    const [leaving, setLeaving] = useState(false);

    const handleLeave = async () => {
        setLeaving(true);
        const result = await leaveGameSession(session.id);
        setLeaving(false);
        if (result._tag === 'Success') {
            toast.success('Left the room');
            navigate('/game-sessions/lobby');
        } else {
            toast.error('Could not leave');
            revalidator.revalidate();
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-wrap items-center gap-3">
                <ButtonLink to="/game-sessions/lobby" variant="secondary">
                    Lobby
                </ButtonLink>
                {isRegisteredUser(user) ? (
                    <ButtonLink to="/my/game-sessions" variant="secondary">
                        My sessions
                    </ButtonLink>
                ) : null}
            </div>

            <h1 className="mb-2 text-2xl font-bold">Room {roomCode}</h1>
            <p className="text-muted mb-6 text-sm">
                Status: {session.status} · Players: {displayCount} /{' '}
                {session.max_players}
            </p>

            <PageIntroExpandable
                summary="You are in a live session room. Player count updates when you are connected to realtime."
                moreLabel="What you can do in the room"
            >
                <p className="text-muted text-sm">
                    Use Leave session to return to the lobby. The participants list
                    reflects who has joined; more host and player actions will appear
                    here as the game flow grows.
                </p>
            </PageIntroExpandable>

            <div className="bg-card mb-6 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <h2 className="mb-2 font-semibold">Participants</h2>
                {session.participants && session.participants.length > 0 ? (
                    <ul className="flex flex-col gap-2">
                        {session.participants.map((p) => (
                            <li key={p.id} className="text-sm">
                                {p.user?.name ?? 'Player'}{' '}
                                <span className="text-muted font-mono">
                                    ({p.user_id ?? 'guest'})
                                </span>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="text-muted text-sm">No participants yet.</p>
                )}
            </div>

            <Button
                type="button"
                variant="secondary"
                disabled={leaving}
                onClick={() => void handleLeave()}
            >
                {leaving ? 'Leaving…' : 'Leave session'}
            </Button>
        </div>
    );
}
