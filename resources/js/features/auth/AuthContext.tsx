import { authManager, AuthState } from '@/features/auth/authManager';
import { isRegisteredUser } from '@/features/auth/authSession';
import { useOnlineStatus } from '@/hooks/useOnlineStatus';
import { devLog } from '@/lib/devLogging';
import { UserData } from '@/schemas/App/Data/Models/UserData';
import { LoginRequest } from '@/schemas/App/Features/Auth/Requests/LoginRequest';
import { RegisterRequest } from '@/schemas/App/Features/Auth/Requests/RegisterRequest';
import {
    createContext,
    ReactNode,
    useContext,
    useEffect,
    useRef,
    useState,
} from 'react';
import toast from 'react-hot-toast';
import { useMatches, useNavigate } from 'react-router';
import {
    disconnectGoogle as apiDisconnectGoogle,
    login as apiLogin,
    logout as apiLogout,
    register as apiRegister,
    showUser,
} from './api';

export interface AuthContextState {
    hasFetchedUser: boolean;
    isAuthenticated: boolean;
    user: UserData | null;
}

interface AuthContextType {
    authState: AuthContextState;
    user: UserData | null;
    login: typeof apiLogin;
    register: typeof apiRegister;
    logout: () => Promise<void>;
    googleLogin: (reconnect?: boolean, remember?: boolean) => Promise<void>;
    disconnectGoogle: typeof apiDisconnectGoogle;
    isLoading: boolean;
    refreshUser: () => ReturnType<typeof showUser>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);
const OFFLINE_AUTH_TRUST_KEY = 'auth_user_trusted_offline';

const setOfflineAuthTrust = (trusted: boolean): void => {
    if (trusted) {
        localStorage.setItem(OFFLINE_AUTH_TRUST_KEY, '1');
        return;
    }

    localStorage.removeItem(OFFLINE_AUTH_TRUST_KEY);
};

const isOfflineAuthTrusted = (): boolean => {
    return localStorage.getItem(OFFLINE_AUTH_TRUST_KEY) === '1';
};

const getInitialAuthState = (): AuthState & { hasFetchedUser: boolean } => {
    const cachedAuthState = authManager.getAuthState();
    const isOffline = typeof navigator !== 'undefined' && !navigator.onLine;

    if (
        isOffline &&
        isOfflineAuthTrusted() &&
        cachedAuthState.isAuthenticated
    ) {
        return { ...cachedAuthState, hasFetchedUser: false };
    }

    return { user: null, isAuthenticated: false, hasFetchedUser: false };
};

interface AuthProviderProps {
    children: ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
    const [authState, setAuthState] = useState<
        AuthState & {
            hasFetchedUser: boolean;
        }
    >(getInitialAuthState);
    const [isLoading, setIsLoading] = useState(false);
    const hasMounted = useRef(false);
    const isOnline = useOnlineStatus();
    const isFetchingUser = useRef(false);
    const userFetchChainRef = useRef<Promise<void>>(Promise.resolve());

    // Provide the current user directly for easier consumption and reactivity
    const user = authState.user;

    const handleSetAuthState = (state: AuthState) => {
        setAuthState((previousState) => {
            const nextState = { ...state, hasFetchedUser: true };

            if (JSON.stringify(previousState) === JSON.stringify(nextState)) {
                return previousState;
            }

            devLog('[AuthContext] Auth state changed:', state);
            return nextState;
        });
    };

    const getUser = (): Promise<void> => {
        userFetchChainRef.current = userFetchChainRef.current
            .catch((error: unknown) => {
                console.error('[AuthContext] User fetch chain error:', error);
            })
            .then(async () => {
                isFetchingUser.current = true;
                try {
                    const result = await showUser();
                    if (result._tag === 'Success') {
                        setOfflineAuthTrust(true);
                        authManager.setUser(result.data);
                        devLog('[AuthContext] Fetched user data');
                    } else if (result._tag === 'AuthenticationError') {
                        setOfflineAuthTrust(false);
                        authManager.clearAuthData();
                    }
                } finally {
                    isFetchingUser.current = false;
                    setAuthState((previousState) => {
                        if (previousState.hasFetchedUser) {
                            return previousState;
                        }

                        return { ...previousState, hasFetchedUser: true };
                    });
                }
            });

        return userFetchChainRef.current;
    };

    const refreshUser = () => {
        return showUser().then((result) => {
            if (result._tag === 'Success') {
                setOfflineAuthTrust(true);
                authManager.setUser(result.data);
            } else if (result._tag === 'AuthenticationError') {
                setOfflineAuthTrust(false);
                authManager.clearAuthData();
            }

            return result;
        });
    };

    useEffect(() => {
        // Avoid flapping during initial boot.
        // Real-time connections are now handled by individual hooks when user changes
        if (!hasMounted.current) {
            hasMounted.current = true;
            return;
        }
    }, [authState.isAuthenticated]);

    useEffect(() => {
        const unsubscribe = authManager.subscribe(handleSetAuthState);
        const urlParams = new URLSearchParams(window.location.search);
        const auth = urlParams.get('auth');
        if (auth === 'success') {
            toast.success('Logged in successfully');
        } else if (auth === 'error') {
            const message =
                urlParams.get('message') ||
                'An error occurred during authentication';
            toast.error(message);
        }
        // Clean up URL
        const url = new URL(window.location.href);
        url.searchParams.delete('auth');
        url.searchParams.delete('message');
        window.history.replaceState({}, document.title, url.toString());
        if (isOnline) {
            getUser();
        } else {
            const cachedAuthState = authManager.getAuthState();

            if (isOfflineAuthTrusted() && cachedAuthState.isAuthenticated) {
                authManager.setUser(cachedAuthState.user);
            } else {
                authManager.clearAuthData();
            }
        }

        return unsubscribe;
    }, [isOnline]);

    const login = async (credentials: LoginRequest) => {
        setIsLoading(true);

        const result = await apiLogin(credentials);
        if (result._tag === 'Success') {
            // Fetch user after successful login
            await getUser();
        }
        setIsLoading(false);
        return result;
    };

    const register = async (data: RegisterRequest) => {
        setIsLoading(true);

        const result = await apiRegister(data);
        if (result._tag === 'Success') {
            // Fetch user after successful register
            await getUser();
        }
        setIsLoading(false);
        return result;
    };

    const logout = async () => {
        const response = await apiLogout();

        if (response._tag === 'Success') {
            // Clear client-side authentication data
            setOfflineAuthTrust(false);
            authManager.clearAuthData();

            toast.success(response.data.message);
        } else {
            toast.error('Failed to log out. Please try again.');
        }
    };

    const googleLogin = async (
        reconnect?: boolean,
        remember?: boolean,
    ): Promise<void> => {
        const params = new URLSearchParams();
        if (reconnect) {
            params.set('reconnect', '1');
        }
        if (remember) {
            params.set('remember', '1');
        }
        const query = params.toString();
        window.location.href = `/auth/google${query ? `?${query}` : ''}`;
    };

    const disconnectGoogle = async () => {
        const result = await apiDisconnectGoogle();
        if (result._tag === 'Success') {
            // Update user data after disconnecting Google
            setOfflineAuthTrust(true);
            authManager.setUser(result.data.user);
            toast.success(result.data.message);
        }
        return result;
    };

    const value: AuthContextType = {
        authState,
        user,
        login,
        register,
        logout,
        googleLogin,
        disconnectGoogle,
        isLoading,
        refreshUser,
    };

    return (
        <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    );
}

export function useAuth(): AuthContextType {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}

// Component to handle navigation when authenticated on auth pages
export function AuthGuard({ children }: { children: ReactNode }) {
    const { authState } = useAuth();
    const navigate = useNavigate();
    const matches = useMatches();

    const isOnAuthPage = matches.some(
        (match) => (match.handle as any)?.isAuthPage,
    );
    const isOnProtectedPage = matches.some(
        (match) => (match.handle as any)?.isProtected,
    );
    const requiresFullAccount = matches.some(
        (match) => (match.handle as any)?.requiresFullAccount === true,
    );

    const registered = isRegisteredUser(authState.user);

    useEffect(() => {
        if (!(isOnProtectedPage || isOnAuthPage) || !authState.hasFetchedUser) {
            return;
        }

        if (registered && isOnAuthPage) {
            navigate('/game-sessions/lobby', { replace: true });
        }

        const needsLogin =
            isOnProtectedPage &&
            !isOnAuthPage &&
            ((!authState.user && authState.hasFetchedUser) ||
                (requiresFullAccount && !registered));

        if (needsLogin) {
            navigate('/login', { replace: true });
        }
    }, [
        authState.user,
        authState.hasFetchedUser,
        isOnAuthPage,
        registered,
        navigate,
        isOnProtectedPage,
        requiresFullAccount,
    ]);

    const allowedOnProtected =
        authState.user && (!requiresFullAccount || registered);

    if (
        isOnProtectedPage &&
        (!authState.hasFetchedUser ||
            (registered && isOnAuthPage) ||
            (!allowedOnProtected && !isOnAuthPage))
    ) {
        return null;
    }

    return <>{children}</>;
}
