import { AuthPageFrame } from '@/components/layout/AuthPageFrame';
import { Surface } from '@/components/layout/Surface';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { useEffect } from 'react';
import { isRouteErrorResponse, useRouteError } from 'react-router-dom';

const AUTH_PROVIDER_ERROR_MESSAGE =
    'useAuth must be used within an AuthProvider';

export function ErrorPage() {
    const error = useRouteError();

    useEffect(() => {
        if (
            error instanceof Error &&
            error.message === AUTH_PROVIDER_ERROR_MESSAGE
        ) {
            const id = setTimeout(() => window.location.reload(), 500);
            return () => clearTimeout(id);
        }
        return undefined;
    }, [error]);

    if (isRouteErrorResponse(error)) {
        return (
            <AuthPageFrame>
                <Surface variant="elevated" className="p-8 sm:p-10">
                    <div className="text-center">
                        <h1 className="text-secondary mb-2 text-5xl font-bold tracking-tight sm:text-6xl">
                            {error.status}
                        </h1>
                        <h2 className="text-secondary mb-4 text-xl font-semibold sm:text-2xl">
                            {error.statusText}
                        </h2>
                        {error.data?.message && (
                            <p className="text-muted mb-8 text-sm leading-relaxed">
                                {error.data.message}
                            </p>
                        )}
                        <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
                            <Button
                                onClick={() => (window.location.href = '/')}
                            >
                                Go home
                            </Button>
                            <ButtonLink
                                to="/game-sessions/lobby"
                                variant="secondary"
                            >
                                Game lobby
                            </ButtonLink>
                            <Button
                                variant="outline"
                                onClick={() => window.location.reload()}
                            >
                                Try again
                            </Button>
                        </div>
                    </div>
                </Surface>
            </AuthPageFrame>
        );
    }

    return (
        <AuthPageFrame>
            <Surface variant="elevated" className="p-8 sm:p-10">
                <div className="text-center">
                    <h1 className="text-secondary mb-2 text-5xl font-bold tracking-tight sm:text-6xl">
                        Oops
                    </h1>
                    <h2 className="text-secondary mb-4 text-xl font-semibold sm:text-2xl">
                        Something went wrong
                    </h2>
                    <p className="text-muted mb-8 text-sm leading-relaxed">
                        {error instanceof Error
                            ? error.message
                            : 'An unexpected error occurred'}
                    </p>
                    <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <Button onClick={() => (window.location.href = '/')}>
                            Go home
                        </Button>
                        <ButtonLink
                            to="/game-sessions/lobby"
                            variant="secondary"
                        >
                            Game lobby
                        </ButtonLink>
                        <Button
                            variant="outline"
                            onClick={() => window.location.reload()}
                        >
                            Try again
                        </Button>
                    </div>
                </div>
            </Surface>
        </AuthPageFrame>
    );
}
