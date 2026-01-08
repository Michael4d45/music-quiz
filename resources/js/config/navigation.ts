import {
    BarChart3,
    Disc,
    FileQuestion,
    Gamepad2,
    Globe,
    Grid3x3,
    LucideIcon,
    Music,
    Music2,
    PlusCircle,
    Search,
    Trophy,
    Users,
} from 'lucide-react';

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
        title: 'Browse',
        items: [
            {
                href: '/browse',
                label: 'Browse',
                icon: Search,
            },
            {
                href: '/browse/categories',
                label: 'Categories',
                icon: Grid3x3,
            },
            {
                href: '/browse/tracks',
                label: 'Tracks',
                icon: Music2,
            },
            {
                href: '/browse/playlists',
                label: 'Public Playlists',
                icon: Globe,
            },
        ],
    },
    {
        title: 'Games',
        items: [
            {
                href: '/active-games',
                label: 'Active Games',
                icon: Gamepad2,
            },
            {
                href: '/sessions/create',
                label: 'Create Game',
                icon: PlusCircle,
            },
            {
                href: '/sessions/join',
                label: 'Join Game',
                icon: Users,
            },
        ],
    },
    {
        title: 'Playlists',
        items: [
            {
                href: '/playlists',
                label: 'My Playlists',
                icon: Music,
            },
            {
                href: '/music-tracks',
                label: 'My Music Tracks',
                icon: Disc,
            },
            {
                href: '/quiz-questions',
                label: 'My Quiz Questions',
                icon: FileQuestion,
            },
        ],
    },
    {
        title: 'Statistics',
        items: [
            {
                href: '/statistics',
                label: 'My Statistics',
                icon: BarChart3,
            },
            {
                href: '/leaderboard',
                label: 'Leaderboard',
                icon: Trophy,
            },
        ],
    },
];
