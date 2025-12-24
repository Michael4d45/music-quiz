import AuthButtons from '@/components/AuthButtons';
import UserDropdown from '@/components/UserDropdown';
import { Link } from '@inertiajs/react';
import { Home, Settings, User } from 'lucide-react';

interface SidebarProps {
    user?: any;
    loggedIn: boolean;
}

export default function Sidebar({ user, loggedIn }: SidebarProps) {
    return (
        <aside className="hidden md:fixed md:inset-y-0 md:z-50 md:flex md:w-64 md:flex-col">
            <div className="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4 dark:border-gray-800 dark:bg-gray-900">
                <div className="flex h-16 shrink-0 items-center">
                    <h1 className="text-xl font-bold text-gray-900 dark:text-white">Music Quiz</h1>
                </div>
                <nav className="flex flex-1 flex-col">
                    <ul role="list" className="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" className="-mx-2 space-y-1">
                                <li>
                                    <Link
                                        href="/"
                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                                    >
                                        <Home className="h-6 w-6 shrink-0" />
                                        Home
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/profile"
                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                                    >
                                        <User className="h-6 w-6 shrink-0" />
                                        Profile
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/preferences"
                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                                    >
                                        <Settings className="h-6 w-6 shrink-0" />
                                        Preferences
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <div className="flex flex-col gap-y-4">{loggedIn ? <UserDropdown user={user} /> : <AuthButtons />}</div>
            </div>
        </aside>
    );
}
