import { AuthPageFrame } from '@/components/layout/AuthPageFrame';
import { Surface } from '@/components/layout/Surface';
import { Button } from '@/components/ui/Button';
import { Form, FormField } from '@/components/ui/Form';
import { GoogleIcon } from '@/components/ui/GoogleIcon';
import { useAuth } from '@/features/auth/AuthContext';
import { useOfflineBlock } from '@/hooks/useOfflineBlock';
import { Link } from 'react-router-dom';

export function RegisterPage() {
    const { register, googleLogin, isLoading } = useAuth();
    const { isBlocked, blockReason } = useOfflineBlock();

    const handleSubmit = async (formData: FormData) => {
        const name = formData.get('name') as string;
        const email = formData.get('email') as string;
        const password = formData.get('password') as string;
        const password_confirmation = formData.get(
            'password_confirmation',
        ) as string;

        return await register({
            name,
            email,
            password,
            password_confirmation,
        });
    };

    const clientValidation = (
        formData: FormData,
    ): Record<string, readonly string[]> => {
        const password = formData.get('password') as string;
        const password_confirmation = formData.get(
            'password_confirmation',
        ) as string;

        const errors: Record<string, readonly string[]> = {};

        if (password.length < 8) {
            errors['password'] = ['Password must be at least 8 characters'];
        }

        if (password !== password_confirmation) {
            errors['password_confirmation'] = ['Passwords do not match'];
        }

        return errors;
    };

    return (
        <AuthPageFrame>
            <Surface variant="elevated" className="p-8 sm:p-10">
                <h1 className="mb-2 text-center text-2xl font-bold tracking-tight">
                    Create Account
                </h1>
                <p className="text-muted mb-8 text-center text-sm">
                    Build playlists and host your own rounds.
                </p>

                {isBlocked && (
                    <div className="border-danger bg-danger-light mb-6 rounded-lg border p-4">
                        <p className="text-danger">{blockReason}</p>
                    </div>
                )}

                <Form
                    onSubmit={handleSubmit}
                    offlineBlock={{ isBlocked, blockReason }}
                    clientValidation={clientValidation}
                    className="space-y-4"
                >
                    <FormField
                        type="text"
                        id="name"
                        name="name"
                        label="Full Name"
                        required
                    />

                    <FormField
                        type="email"
                        id="email"
                        name="email"
                        label="Email"
                        required
                    />

                    <FormField
                        type="password"
                        id="password"
                        name="password"
                        label="Password"
                        required
                        minLength={8}
                    />

                    <FormField
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        label="Confirm Password"
                        required
                    />

                    <Button
                        data-test="create-account"
                        type="submit"
                        className="w-full"
                        disabled={isLoading || isBlocked}
                    >
                        {isLoading ? 'Creating Account...' : 'Create Account'}
                    </Button>
                </Form>

                <div className="mt-8">
                    <div className="relative">
                        <div className="absolute inset-0 flex items-center">
                            <div className="border-secondary w-full border-t" />
                        </div>
                        <div className="relative flex justify-center text-sm">
                            <span className="bg-card text-secondary px-3">
                                Or
                            </span>
                        </div>
                    </div>

                    <div className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => googleLogin()}
                            className="flex w-full items-center justify-center gap-2"
                            disabled={isLoading || isBlocked}
                        >
                            <GoogleIcon />
                            Continue with Google
                        </Button>
                    </div>
                </div>

                <div className="mt-8 text-center">
                    <p className="text-secondary text-sm">
                        Already have an account?{' '}
                        <Link
                            to="/login"
                            className="text-primary hover:text-primary-hover font-medium"
                        >
                            Sign in
                        </Link>
                    </p>
                </div>
            </Surface>
        </AuthPageFrame>
    );
}
