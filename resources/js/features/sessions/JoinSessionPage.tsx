import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { redirect, useNavigate } from 'react-router-dom';
import { useState } from 'react';

export async function joinSessionLoader() {
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
        return redirect('/login');
    }

    return {};
}

export function JoinSessionPage() {
    const navigate = useNavigate();
    const [isJoining, setIsJoining] = useState(false);
    const [roomCode, setRoomCode] = useState('');
    const [error, setError] = useState('');

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!roomCode.trim()) {
            setError('Please enter a room code');
            return;
        }

        setIsJoining(true);
        setError('');

        try {
            const result = await ApiClient.joinSession({
                room_code: roomCode.trim().toUpperCase(),
                guest_name: null,
            });

            if (result._tag === 'Success') {
                navigate(`/sessions/${roomCode.trim().toUpperCase()}`);
            } else {
                setError('Failed to join game. Please check the room code and try again.');
            }
        } catch (err) {
            setError('Failed to join game. Please try again.');
        } finally {
            setIsJoining(false);
        }
    };

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-md">
                <h1 className="text-3xl font-bold mb-8 text-center">Join a Game</h1>

                <form onSubmit={handleSubmit} className="bg-card rounded-lg p-6 shadow-lg">
                    <div className="mb-4">
                        <label htmlFor="room_code" className="block text-sm font-medium mb-2">
                            Room Code
                        </label>
                        <input
                            type="text"
                            id="room_code"
                            value={roomCode}
                            onChange={(e) => setRoomCode(e.target.value.toUpperCase())}
                            placeholder="Enter room code"
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-(--primary) uppercase"
                            maxLength={10}
                            required
                        />
                        <p className="text-xs text-muted mt-1">
                            Enter the room code shared by the game host
                        </p>
                    </div>

                    {error && (
                        <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p className="text-red-600 text-sm">{error}</p>
                        </div>
                    )}

                    <button
                        type="submit"
                        disabled={isJoining || !roomCode.trim()}
                        className="w-full btn-success rounded-lg py-3 transition-colors disabled:opacity-50"
                    >
                        {isJoining ? 'Joining...' : 'Join Game'}
                    </button>
                </form>

                <div className="mt-6 text-center">
                    <p className="text-muted text-sm mb-2">Don't have a room code?</p>
                    <a
                        href="/active-games"
                        className="text-(--primary) hover:underline"
                    >
                        Browse active games
                    </a>
                </div>
            </div>
        </div>
    );
}