import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { useLoaderData, useParams, useNavigate } from 'react-router-dom';
import { SessionLobbyResponse } from '@/types/effect-schemas';
import { useEffect, useState } from 'react';

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

    const session = data.session;
    const isHost = session.host_id === authManager.getUser()?.id;

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
                    <h1 className="text-4xl font-bold font-mono mb-2">
                        {session.room_code}
                    </h1>
                    <p className="text-muted">
                        Game Lobby - {(session.quiz_mode as any)?.name}
                    </p>
                </div>

                <div className="bg-card rounded-lg p-6 shadow-lg mb-6">
                    <h2 className="text-xl font-semibold mb-4">Game Settings</h2>
                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span className="text-muted">Host:</span>
                            <span className="ml-2 font-medium">{(session.host as any)?.name}</span>
                        </div>
                        <div>
                            <span className="text-muted">Max Players:</span>
                            <span className="ml-2 font-medium">{session.max_players}</span>
                        </div>
                        <div>
                            <span className="text-muted">Quiz Mode:</span>
                            <span className="ml-2 font-medium">{(session.quiz_mode as any)?.name}</span>
                        </div>
                        <div>
                            <span className="text-muted">Scoring:</span>
                            <span className="ml-2 font-medium">{(session.scoring_rule as any)?.name}</span>
                        </div>
                        {(session.playlist as any) && (
                            <div className="col-span-2">
                                <span className="text-muted">Playlist:</span>
                                <span className="ml-2 font-medium">{(session.playlist as any)?.name || 'Unknown'}</span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="bg-card rounded-lg p-6 shadow-lg mb-6">
                    <h2 className="text-xl font-semibold mb-4">
                        Players ({(session.participants as any)?.length || 0}/{session.max_players})
                    </h2>
                    <div className="space-y-2">
                        {(session.participants as any)?.map((participant: any) => (
                            <div key={participant.id} className="flex items-center justify-between p-3 bg-muted rounded">
                                <span className="font-medium">{participant.user?.name}</span>
                                {(participant).is_ready && (
                                    <span className="text-green-600 text-sm">Ready</span>
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="flex gap-4 justify-center">
                    {isHost && session.status === 'lobby' && (
                        <button
                            onClick={handleStartGame}
                            disabled={isStarting}
                            className="btn-success rounded-lg px-6 py-3 transition-colors disabled:opacity-50"
                        >
                            {isStarting ? 'Starting...' : 'Start Game'}
                        </button>
                    )}

                    <button
                        onClick={handleLeaveGame}
                        className="btn-secondary rounded-lg px-6 py-3 transition-colors"
                    >
                        Leave Game
                    </button>
                </div>

                {session.status !== 'lobby' && (
                    <div className="text-center mt-4">
                        <p className="text-muted">
                            Game is {session.status.toLowerCase()}...
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}