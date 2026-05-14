import { AuthPageFrame } from '@/components/layout/AuthPageFrame';
import { Surface } from '@/components/layout/Surface';
import { Button } from '@/components/ui/Button';
import { Form, FormField } from '@/components/ui/Form';
import { sendPasswordResetLink } from '@/features/auth/api';
import { useAuth } from '@/features/auth/AuthContext';
import { useOfflineBlock } from '@/hooks/useOfflineBlock';
import { useState } from 'react';
import { Link } from 'react-router-dom';

export function ForgotPasswordPage() {
    const [isSubmitted, setIsSubmitted] = useState(false);
    const { isLoading } = useAuth();
    const { isBlocked, blockReason } = useOfflineBlock();

    const handleSubmit = async (formData: FormData) => {
        const email = formData.get('email') as string;
        return await sendPasswordResetLink(email);
    };

    const handleSuccess = () => {
        setIsSubmitted(true);
    };

    if (isSubmitted) {
        return (
            <AuthPageFrame>
                <Surface variant="elevated" className="p-8 sm:p-10">
                    <h1 className="mb-4 text-center text-2xl font-bold tracking-tight">
                        Check Your Email
                    </h1>
                    <p className="text-secondary mb-8 text-center text-sm leading-relaxed">
                        If an account exists with that email, a password reset
                        link has been sent.
                    </p>
                    <div className="text-center">
                        <Link
                            to="/login"
                            className="text-primary hover:text-primary-hover font-medium"
                        >
                            Back to Login
                        </Link>
                    </div>
                </Surface>
            </AuthPageFrame>
        );
    }

    return (
        <AuthPageFrame>
            <Surface variant="elevated" className="p-8 sm:p-10">
                <h1 className="mb-2 text-center text-2xl font-bold tracking-tight">
                    Forgot Password
                </h1>
                <p className="text-muted mb-8 text-center text-sm">
                    We will email you a reset link—quick and painless.
                </p>

                {isBlocked && (
                    <div className="border-danger bg-danger-light mb-6 rounded-lg border p-4">
                        <p className="text-danger">{blockReason}</p>
                    </div>
                )}

                <Form
                    onSubmit={handleSubmit}
                    onSuccess={handleSuccess}
                    offlineBlock={{ isBlocked, blockReason }}
                    className="space-y-4"
                >
                    <FormField
                        type="email"
                        id="email"
                        name="email"
                        label="Email"
                        required
                    />

                    <Button
                        type="submit"
                        className="w-full"
                        disabled={isLoading || isBlocked}
                        data-test="send-reset-link"
                    >
                        {isLoading ? 'Sending...' : 'Send Reset Link'}
                    </Button>
                </Form>

                <div className="mt-6 text-center">
                    <p className="text-secondary text-sm">
                        Remember your password?{' '}
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
