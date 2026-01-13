import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { echo } from '@/lib/echo';
import { SessionLobbyResponse } from '@/schemas/App/Data/Response';
import { useEffect, useState } from 'react';
import { useLoaderData, useNavigate, useParams } from 'react-router-dom';

export async function sessionLobbyLoader({ params }: any) {
    let user = authManager.getUser();
    let token = authManager.getToken();

    // Try to restore from session if no local token
    if (!user || !token) {
        const result = await ApiClient.fetchSessionToken();
        if (result._tag === 'Success') {
            authManager.setAuthData(result.data.token, result.data.user);
            user = result.data.user;
            token = result.data.token;
        }
    }

    if (!user || !token) {
        throw new Error('Unauthorized');
    }

    const result = await ApiClient.showSessionLobby(params.roomCode);
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load session lobby');
}

export function SessionLobbyPage() {
    const data = useLoaderData<SessionLobbyResponse>();
    const { roomCode } = useParams<{ roomCode: string }>();
    const navigate = useNavigate();
    const [isStarting, setIsStarting] = useState(false);
    const [session, setSession] = useState(data.session);

    const isHost = session.host_id === authManager.getUser()?.id;

    useEffect(() => {
        if (!roomCode) return;

        const channel = echo.join(`session.${roomCode}`);

        channel.listen('PlayerJoined', (event: any) => {
            console.log('Player joined:', event);
            // Refresh lobby data
            ApiClient.showSessionLobby(roomCode).then((result) => {
                if (result._tag === 'Success') {
                    setSession(result.data.session);
                }
            });
        });

        channel.listen('PlayerLeft', (event: any) => {
            console.log('Player left:', event);
            // Refresh lobby data
            ApiClient.showSessionLobby(roomCode).then((result) => {
                if (result._tag === 'Success') {
                    setSession(result.data.session);
                }
            });
        });

        channel.listen('RoundStarted', (event: any) => {
            console.log('Round started:', event);
            navigate(`/sessions/${roomCode}/play`);
        });

        return () => {
            echo.leave(`session.${roomCode}`);
        };
    }, [roomCode, navigate]);

    const handleStartGame = async () => {
        if (!roomCode) return;

        setIsStarting(true);
        try {
            const result = await ApiClient.startSession(roomCode);
            if (result._tag === 'Success') {
                navigate(`/sessions/${roomCode}/play`);
            } else {
                console.error('Failed to start game:', result);
                // Handle error
            }
        } catch (error) {
            console.error('Error starting game:', error);
        } finally {
            setIsStarting(false);
        }
    };

    const handleLeaveGame = async () => {
        if (!roomCode) return;

        try {
            await ApiClient.leaveSession(roomCode);
            navigate('/active-games');
        } catch (error) {
            console.error('Error leaving game:', error);
        }
    };

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-2xl">
                <div className="mb-8 text-center">
                    <h1 className="mb-2 font-mono text-4xl font-bold">
                        {session.room_code}
                    </h1>
                    <p className="text-muted">
                        Game Lobby - {(session.quiz_mode as any)?.name}
                    </p>
                </div>

                <div className="bg-card mb-6 rounded-lg p-6 shadow-lg">
                    <h2 className="mb-4 text-xl font-semibold">
                        Game Settings
                    </h2>
                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span className="text-muted">Host:</span>
                            <span className="ml-2 font-medium">
                                {(session.host as any)?.name}
                            </span>
                        </div>
                        <div>
                            <span className="text-muted">Max Players:</span>
                            <span className="ml-2 font-medium">
                                {session.max_players}
                            </span>
                        </div>
                        <div>
                            <span className="text-muted">Quiz Mode:</span>
                            <span className="ml-2 font-medium">
                                {(session.quiz_mode as any)?.name}
                            </span>
                        </div>
                        <div>
                            <span className="text-muted">Scoring:</span>
                            <span className="ml-2 font-medium">
                                {(session.scoring_rule as any)?.name}
                            </span>
                        </div>
                        {(session.playlist as any) && (
                            <div className="col-span-2">
                                <span className="text-muted">Playlist:</span>
                                <span className="ml-2 font-medium">
                                    {(session.playlist as any)?.name ||
                                        'Unknown'}
                                </span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="bg-card mb-6 rounded-lg p-6 shadow-lg">
                    <h2 className="mb-4 text-xl font-semibold">
                        Players ({(session.participants as any)?.length || 0}/
                        {session.max_players})
                    </h2>
                    <div className="space-y-2">
                        {(session.participants as any)?.map(
                            (participant: any) => (
                                <div
                                    key={participant.id}
                                    className="bg-muted flex items-center justify-between rounded p-3"
                                >
                                    <span className="font-medium">
                                        {participant.user?.name ||
                                            participant.guest_name}
                                    </span>
                                </div>
                            ),
                        )}
                    </div>
                </div>

                <div className="flex justify-center gap-4">
                    {isHost && session.status === 'lobby' && (
                        <button
                            onClick={handleStartGame}
                            disabled={isStarting}
                            className="rounded-lg bg-green-600 px-6 py-3 text-white transition-colors hover:bg-green-700 disabled:opacity-50"
                        >
                            {isStarting ? 'Starting...' : 'Start Game'}
                        </button>
                    )}

                    <button
                        onClick={handleLeaveGame}
                        className="rounded-lg bg-gray-600 px-6 py-3 text-white transition-colors hover:bg-gray-700"
                    >
                        Leave Game
                    </button>
                </div>

                {session.status !== 'lobby' && (
                    <div className="mt-4 text-center">
                        <p className="text-muted">
                            Game is {session.status.toLowerCase()}...
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}
