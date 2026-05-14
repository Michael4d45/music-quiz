import {
    Gamepad2,
    Home,
    ListMusic,
    ListVideo,
    Mic2,
    type LucideIcon,
} from 'lucide-react';

export interface NavigationItem {
    href: string;
    label: string;
    icon: LucideIcon;
    /** When true, only shown to registered (non-guest) users */
    requiresRegistered?: boolean;
}

export interface NavigationSection {
    title: string;
    items: NavigationItem[];
}

export const navigationSections: NavigationSection[] = [
    {
        title: 'Main',
        items: [
            {
                href: '/',
                label: 'Home',
                icon: Home,
            },
            {
                href: '/game-sessions/lobby',
                label: 'Game lobby',
                icon: Gamepad2,
            },
            {
                href: '/my/game-sessions',
                label: 'My sessions',
                icon: Gamepad2,
                requiresRegistered: true,
            },
            {
                href: '/my/playlists',
                label: 'My playlists',
                icon: ListVideo,
                requiresRegistered: true,
            },
            {
                href: '/my/quiz-questions',
                label: 'My quiz questions',
                icon: Mic2,
                requiresRegistered: true,
            },
            {
                href: '/my/music-tracks',
                label: 'My tracks',
                icon: ListMusic,
                requiresRegistered: true,
            },
        ],
    },
];
