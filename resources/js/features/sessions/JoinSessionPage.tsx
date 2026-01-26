import { ApiClient } from '@/lib/apiClient';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

export async function joinSessionLoader() {
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
                setError(
                    'Failed to join game. Please check the room code and try again.',
                );
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
                <h1 className="mb-8 text-center text-3xl font-bold">
                    Join a Game
                </h1>

                <form
                    onSubmit={handleSubmit}
                    className="bg-card rounded-lg p-6 shadow-lg"
                >
                    <div className="mb-4">
                        <label
                            htmlFor="room_code"
                            className="mb-2 block text-sm font-medium"
                        >
                            Room Code
                        </label>
                        <input
                            type="text"
                            id="room_code"
                            value={roomCode}
                            onChange={(e) =>
                                setRoomCode(e.target.value.toUpperCase())
                            }
                            placeholder="Enter room code"
                            className="w-full rounded-lg border px-3 py-2 uppercase focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            maxLength={10}
                            required
                        />
                        <p className="text-muted mt-1 text-xs">
                            Enter the room code shared by the game host
                        </p>
                    </div>

                    {error && (
                        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
                            <p className="text-sm text-red-600">{error}</p>
                        </div>
                    )}

                    <button
                        type="submit"
                        disabled={isJoining || !roomCode.trim()}
                        className="btn-success w-full rounded-lg py-3 transition-colors disabled:opacity-50"
                    >
                        {isJoining ? 'Joining...' : 'Join Game'}
                    </button>
                </form>

                <div className="mt-6 text-center">
                    <p className="text-muted mb-2 text-sm">
                        Don't have a room code?
                    </p>
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
