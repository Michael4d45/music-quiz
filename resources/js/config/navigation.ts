import { Gamepad2, Home, type LucideIcon } from 'lucide-react';

export interface NavigationItem {
    href: string;
    label: string;
    icon: LucideIcon;
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
        ],
    },
];
