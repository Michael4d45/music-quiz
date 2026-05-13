import type { UserData } from '@/schemas/App/Data/Models/UserData';

/**
 * Whether the SPA has a resolved session user from `/api/user` (guest or registered).
 */
export function hasSessionUser(user: UserData | null): boolean {
    return user !== null;
}

/**
 * Whether the session user is a full account (not a server-side guest placeholder).
 */
export function isRegisteredUser(user: UserData | null): user is UserData {
    return user !== null && !user.is_guest;
}

/**
 * Show Log in / Sign up in chrome (e.g. sidebar): no session user yet, guest, or session fetch failed.
 */
export function showPublicAuthLinks(user: UserData | null): boolean {
    return user === null || user.is_guest;
}
