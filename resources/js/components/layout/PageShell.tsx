import { cn } from '@/lib/utils';
import type { HTMLAttributes, ReactNode } from 'react';

const maxWidthClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '4xl': 'max-w-4xl',
    '7xl': 'max-w-7xl',
    full: 'max-w-full',
} as const;

export type PageShellMaxWidth = keyof typeof maxWidthClasses;

export interface PageShellProps extends HTMLAttributes<HTMLDivElement> {
    readonly children: ReactNode;
    readonly maxWidth?: PageShellMaxWidth;
}

/**
 * Consistent horizontal padding and width for SPA pages (continuous layout shell).
 */
export function PageShell({
    children,
    className,
    maxWidth = '4xl',
    ...props
}: PageShellProps) {
    return (
        <div
            className={cn(
                'mx-auto w-full px-4 py-6 sm:px-5',
                maxWidthClasses[maxWidth],
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}
