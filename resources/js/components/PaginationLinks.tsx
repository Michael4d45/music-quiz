import { cn } from '@/lib/utils';
import { Link, useSearchParams } from 'react-router-dom';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface PaginationLinksProps {
    meta: PaginationMeta;
    baseUrl?: string;
}

export default function PaginationLinks({
    meta,
    baseUrl = '',
}: PaginationLinksProps) {
    const [searchParams] = useSearchParams();

    if (meta.last_page <= 1) {
        return null;
    }

    const createPageUrl = (page: number | null) => {
        if (page === null) return '#';
        const params = new URLSearchParams(searchParams);
        params.set('page', page.toString());
        return `${baseUrl}?${params.toString()}`;
    };

    return (
        <div className="mt-6 flex items-center justify-between">
            <div className="text-secondary text-sm">
                Showing {meta.from ?? 0} to {meta.to ?? 0} of {meta.total}{' '}
                results
            </div>
            <div className="flex gap-2">
                <PaginationLink
                    to={createPageUrl(
                        meta.current_page > 1 ? meta.current_page - 1 : null,
                    )}
                    disabled={meta.current_page <= 1}
                >
                    Previous
                </PaginationLink>
                {Array.from({ length: meta.last_page }, (_, i) => i + 1)
                    .filter(
                        (page) =>
                            page === 1 ||
                            page === meta.last_page ||
                            (page >= meta.current_page - 2 &&
                                page <= meta.current_page + 2),
                    )
                    .map((page, index, array) => (
                        <div key={page} className="flex items-center gap-2">
                            {index > 0 && array[index - 1] !== page - 1 && (
                                <span className="text-muted px-2">...</span>
                            )}
                            <PaginationLink
                                to={createPageUrl(page)}
                                isCurrent={page === meta.current_page}
                            >
                                {page}
                            </PaginationLink>
                        </div>
                    ))}
                <PaginationLink
                    to={createPageUrl(
                        meta.current_page < meta.last_page
                            ? meta.current_page + 1
                            : null,
                    )}
                    disabled={meta.current_page >= meta.last_page}
                >
                    Next
                </PaginationLink>
            </div>
        </div>
    );
}

interface PaginationLinkProps {
    to: string;
    disabled?: boolean;
    isCurrent?: boolean;
    children: React.ReactNode;
}

function PaginationLink({
    to,
    disabled,
    isCurrent,
    children,
}: PaginationLinkProps) {
    const baseClasses =
        'px-3 py-2 text-sm font-medium rounded-md transition-colors';

    if (disabled || to === '#') {
        return (
            <span
                className={cn(
                    baseClasses,
                    'text-muted cursor-not-allowed opacity-50',
                )}
            >
                {children}
            </span>
        );
    }

    return (
        <Link
            to={to}
            className={cn(
                baseClasses,
                isCurrent
                    ? 'bg-(--primary) text-white'
                    : 'text-secondary hover-bg-secondary',
            )}
        >
            {children}
        </Link>
    );
}
