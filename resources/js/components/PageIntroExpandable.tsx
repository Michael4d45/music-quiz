import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

export interface PageIntroExpandableProps {
    /** One short line shown above the fold. */
    readonly summary: string;
    /** Optional custom label for the disclosure control. */
    readonly moreLabel?: string;
    /** Longer guidance shown when expanded. */
    readonly children: ReactNode;
    readonly className?: string;
}

/**
 * Compact page intro: a short summary plus optional detail in a native
 * `<details>` so long copy does not dominate small viewports.
 */
export function PageIntroExpandable({
    summary,
    moreLabel = 'More about this screen',
    children,
    className,
}: PageIntroExpandableProps) {
    return (
        <div className={cn('mb-6 max-w-2xl', className)}>
            <p className="text-muted text-sm">{summary}</p>
            <details className="text-muted mt-1 text-sm">
                <summary
                    className={cn(
                        'text-primary hover:text-primary-hover cursor-pointer list-none font-medium underline-offset-2 hover:underline',
                        'marker:hidden [&::-webkit-details-marker]:hidden',
                    )}
                >
                    {moreLabel}
                </summary>
                <div className="border-border mt-2 space-y-2 border-l-2 pl-3 text-sm dark:border-white/20">
                    {children}
                </div>
            </details>
        </div>
    );
}
