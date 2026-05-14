import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

export interface PageHeaderProps {
    readonly title: string;
    readonly description?: ReactNode;
    readonly actions?: ReactNode;
    readonly className?: string;
}

/**
 * Top-of-page title row: typography-led hierarchy with optional actions.
 */
export function PageHeader({
    title,
    description,
    actions,
    className,
}: PageHeaderProps) {
    return (
        <header
            className={cn(
                'mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between',
                className,
            )}
        >
            <div className="min-w-0 space-y-2">
                <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                    {title}
                </h1>
                {description ? (
                    <div className="text-muted max-w-2xl text-base leading-relaxed">
                        {description}
                    </div>
                ) : null}
            </div>
            {actions ? (
                <div className="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                    {actions}
                </div>
            ) : null}
        </header>
    );
}
