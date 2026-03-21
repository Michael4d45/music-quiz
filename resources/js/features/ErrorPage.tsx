import { Button } from '@/components/ui/Button';
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
            <div className="bg-primary flex min-h-dvh items-center justify-center">
                <div className="bg-card w-full max-w-md rounded-lg p-8 shadow-lg">
                    <div className="text-center">
                        <h1 className="text-secondary mb-4 text-6xl font-bold">
                            {error.status}
                        </h1>
                        <h2 className="text-secondary mb-4 text-2xl font-semibold">
                            {error.statusText}
                        </h2>
                        {error.data?.message && (
                            <p className="text-secondary mb-6">
                                {error.data.message}
                            </p>
                        )}
                        <div className="space-x-4">
                            <Button
                                onClick={() => (window.location.href = '/')}
                            >
                                Go Home
                            </Button>
                            <Button
                                variant="secondary"
                                onClick={() => window.location.reload()}
                            >
                                Try Again
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // For non-route errors (like thrown errors from loaders)
    return (
        <div className="bg-primary flex min-h-dvh items-center justify-center">
            <div className="bg-card w-full max-w-md rounded-lg p-8 shadow-lg">
                <div className="text-center">
                    <h1 className="text-secondary mb-4 text-6xl font-bold">
                        Oops!
                    </h1>
                    <h2 className="text-secondary mb-4 text-2xl font-semibold">
                        Something went wrong
                    </h2>
                    <p className="text-secondary mb-6">
                        {error instanceof Error
                            ? error.message
                            : 'An unexpected error occurred'}
                    </p>
                    <div className="space-x-4">
                        <Button onClick={() => (window.location.href = '/')}>
                            Go Home
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={() => window.location.reload()}
                        >
                            Try Again
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
}
