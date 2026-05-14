import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'react';

export type SurfaceVariant = 'none' | 'tint' | 'emphasis' | 'elevated';

const variantClasses: Record<SurfaceVariant, string> = {
    none: '',
    tint: 'rounded-2xl bg-black/[0.025] p-4 sm:p-5 dark:bg-white/[0.04]',
    emphasis:
        'rounded-2xl border-2 border-dashed border-primary/35 bg-black/[0.02] p-4 sm:p-5 dark:border-primary/45 dark:bg-white/[0.03]',
    elevated:
        'bg-card rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-5 dark:shadow-[0_1px_3px_rgba(0,0,0,0.35)] dark:ring-white/10',
};

export interface SurfaceProps extends HTMLAttributes<HTMLDivElement> {
    readonly variant?: SurfaceVariant;
}

/**
 * Semantic surface for grouping content without defaulting to heavy card chrome.
 * Prefer `tint` for in-flow panels; reserve `elevated` for focused or modal-like regions.
 */
export function Surface({
    className,
    variant = 'tint',
    ...props
}: SurfaceProps) {
    return (
        <div
            className={cn(
                variant !== 'none' && variantClasses[variant],
                className,
            )}
            {...props}
        />
    );
}
