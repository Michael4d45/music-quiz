import { Link } from '@inertiajs/react';
import { ChevronDown, LogOut, Settings, User } from 'lucide-react';
import { useState } from 'react';
import AppearanceToggleTab from './AppearanceToggleTab';

interface UserDropdownProps {
    user: Models.UserData;
}

export default function UserDropdown({ user }: UserDropdownProps) {
    const [isOpen, setIsOpen] = useState(false);

    return (
        <div className="relative">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="flex items-center gap-x-2 rounded-full bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-700"
            >
                <span>{user.name}</span>
                <ChevronDown className="h-4 w-4" />
            </button>

            {isOpen && (
                <>
                    <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />
                    <div className="ring-opacity-5 absolute right-0 z-20 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black focus:outline-none dark:bg-gray-800 dark:ring-gray-700">
                        <div className="border-b border-gray-200 px-4 py-2 dark:border-gray-700">
                            <div className="flex items-center gap-x-3">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gray-300 dark:bg-gray-600">
                                    <User className="h-4 w-4 text-gray-600 dark:text-gray-300" />
                                </div>
                                <div>
                                    <div className="text-sm font-medium text-gray-900 dark:text-white">{user.name}</div>
                                    <div className="text-sm text-gray-500 dark:text-gray-400">{user.email}</div>
                                </div>
                            </div>
                        </div>

                        <div className="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                            <AppearanceToggleTab showText={false} />
                        </div>

                        <div className="py-1">
                            <Link
                                href="/profile"
                                className="flex items-center gap-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                                onClick={() => setIsOpen(false)}
                            >
                                <User className="h-4 w-4" />
                                Profile
                            </Link>
                            <Link
                                href="/preferences"
                                className="flex items-center gap-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                                onClick={() => setIsOpen(false)}
                            >
                                <Settings className="h-4 w-4" />
                                Preferences
                            </Link>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="flex w-full items-center gap-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                                onClick={() => setIsOpen(false)}
                            >
                                <LogOut className="h-4 w-4" />
                                Sign out
                            </Link>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
