import { AuthPageFrame } from '@/components/layout/AuthPageFrame';
import { Surface } from '@/components/layout/Surface';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { Link, useNavigate } from 'react-router-dom';

export function NotFoundPage() {
    const navigate = useNavigate();

    const handleGoBack = () => {
        if (window.history.length > 1) {
            navigate(-1);
        } else {
            navigate('/');
        }
    };

    return (
        <AuthPageFrame>
            <Surface variant="elevated" className="p-8 sm:p-10">
                <div className="text-center">
                    <p className="text-primary mb-2 text-sm font-semibold tracking-wide uppercase">
                        Lost in the playlist
                    </p>
                    <h1 className="text-secondary mb-3 text-5xl font-bold tracking-tight sm:text-6xl">
                        404
                    </h1>
                    <h2 className="text-secondary mb-4 text-xl font-semibold sm:text-2xl">
                        Page Not Found
                    </h2>
                    <p className="text-muted mb-10 text-sm leading-relaxed">
                        Sorry, the page you are looking for could not be found.
                        That URL does not match any route—double-check the link
                        or head back to a known screen.
                    </p>

                    <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <Link to="/" className="block sm:inline-block">
                            <Button
                                className="w-full sm:w-auto"
                                data-test="go-home"
                            >
                                Go Home
                            </Button>
                        </Link>
                        <ButtonLink
                            to="/game-sessions/lobby"
                            variant="secondary"
                            className="w-full sm:w-auto"
                        >
                            Game lobby
                        </ButtonLink>
                        <Button
                            variant="outline"
                            className="w-full sm:w-auto"
                            onClick={handleGoBack}
                        >
                            Go Back
                        </Button>
                    </div>
                </div>
            </Surface>
        </AuthPageFrame>
    );
}
