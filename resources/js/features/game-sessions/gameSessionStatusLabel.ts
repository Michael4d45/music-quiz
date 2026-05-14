/**
 * Human-readable labels for `GameSessionData.status` wire values (snake_case).
 */
export function gameSessionStatusLabel(status: string): string {
    const labels: Record<string, string> = {
        lobby: 'Waiting in lobby',
        in_progress: 'In progress',
        round_transition: 'Between rounds',
        paused: 'Paused',
        completed: 'Completed',
    };
    const known = labels[status];
    if (known !== undefined) {
        return known;
    }
    return status
        .split('_')
        .map((part) =>
            part.length === 0
                ? part
                : part.charAt(0).toUpperCase() + part.slice(1).toLowerCase(),
        )
        .join(' ');
}
