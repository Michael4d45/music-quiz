import { Link } from '@inertiajs/react';
import { Home, User, Settings } from 'lucide-react';

export default function Sidebar() {
    return (
        <aside className="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 md:z-50">
            <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 border-r border-gray-200 dark:bg-gray-900 dark:border-gray-800">
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
                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800"
                                    >
                                        <Home className="h-6 w-6 shrink-0" />
                                        Home
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/profile"
                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800"
                                    >
                                        <User className="h-6 w-6 shrink-0" />
                                        Profile
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href="/preferences"
                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800"
                                    >
                                        <Settings className="h-6 w-6 shrink-0" />
                                        Preferences
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
    );
}
