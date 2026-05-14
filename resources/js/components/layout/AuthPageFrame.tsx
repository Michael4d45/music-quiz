import type { ReactNode } from 'react';

export interface AuthPageFrameProps {
    readonly children: ReactNode;
}

/**
 * Centered full-viewport canvas for sign-in and other focused flows.
 */
export function AuthPageFrame({ children }: AuthPageFrameProps) {
    return (
        <div className="bg-primary relative flex min-h-dvh w-full flex-col items-center justify-center px-4 py-12 sm:px-6">
            <div
                className="pointer-events-none absolute inset-0 overflow-hidden"
                aria-hidden
            >
                <div className="bg-primary/30 absolute -top-28 left-[15%] size-72 rounded-full blur-3xl" />
                <div className="bg-info/15 absolute right-[10%] bottom-[-10%] size-[28rem] rounded-full blur-3xl" />
            </div>
            <div className="relative w-full max-w-md">{children}</div>
        </div>
    );
}
