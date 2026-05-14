import { RealtimeNotifications } from '@/components/realtime/RealtimeNotifications';
import { useAuth } from '@/features/auth/AuthContext';
import { isRegisteredUser } from '@/features/auth/authSession';
import { Link } from 'react-router-dom';

export function HomePage() {
    const { user, authState } = useAuth();

    if (!authState.hasFetchedUser) {
        return (
            <div className="bg-primary text-muted flex min-h-dvh items-center justify-center p-6 text-sm">
                Loading…
            </div>
        );
    }

    if (!isRegisteredUser(user)) {
        const guestBrowsing = user?.is_guest === true;
        return (
            <div className="mx-auto flex h-full min-h-full w-full max-w-7xl">
                <div className="flex w-full flex-1 flex-col items-center justify-center px-4 text-center">
                    <h1 className="text-4xl">Welcome</h1>
                    <p className="text-muted mt-4 max-w-lg text-lg">
                        {guestBrowsing
                            ? "You're browsing as a guest. Open the lobby to join a room with a code, or sign in with a full account to host sessions and manage playlists."
                            : 'Get started by signing in or creating an account'}
                    </p>
                    <div className="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:flex-wrap sm:justify-center">
                        {guestBrowsing ? (
                            <Link
                                to="/game-sessions/lobby"
                                className="btn-primary px-6 py-3"
                            >
                                Game lobby
                            </Link>
                        ) : null}
                        <div className="flex gap-4">
                            <Link to="/login" className="btn-primary px-6 py-3">
                                Log in
                            </Link>
                            <Link
                                to="/register"
                                className="btn-secondary px-6 py-3"
                            >
                                Sign up
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto space-y-8 px-4 py-8">
            <div>
                <h1>Welcome back, {user.name}!</h1>
                <p className="text-muted mt-2">
                    You're all set to get started.
                </p>
            </div>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    to="/my/game-sessions"
                    className="card block p-6 transition hover:shadow-lg"
                >
                    <h2 className="mb-2 text-xl font-semibold">Host games</h2>
                    <p className="text-muted">
                        Create a room, pick scoring and quiz mode, and share the
                        code with players
                    </p>
                </Link>

                <Link
                    to="/game-sessions/lobby"
                    className="card block p-6 transition hover:shadow-lg"
                >
                    <h2 className="mb-2 text-xl font-semibold">Game lobby</h2>
                    <p className="text-muted">
                        Join public games that have not started yet
                    </p>
                </Link>

                <Link
                    to="/profile"
                    className="card block p-6 transition hover:shadow-lg"
                >
                    <h2 className="mb-2 text-xl font-semibold">Profile</h2>
                    <p className="text-muted">Manage your account settings</p>
                </Link>
            </div>

            <RealtimeNotifications />
        </div>
    );
}
