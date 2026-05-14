/**
 * Log only in Vite dev builds so production bundles stay quiet in the console.
 */
export function devLog(...args: Parameters<typeof console.log>): void {
    if (import.meta.env.DEV) {
        console.log(...args);
    }
}

export function devInfo(...args: Parameters<typeof console.info>): void {
    if (import.meta.env.DEV) {
        console.info(...args);
    }
}

export function devWarn(...args: Parameters<typeof console.warn>): void {
    if (import.meta.env.DEV) {
        console.warn(...args);
    }
}
