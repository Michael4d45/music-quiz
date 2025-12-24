import { useState } from 'react';
import { Menu, X } from 'lucide-react';

interface HeaderProps {
    children?: React.ReactNode;
}

export default function Header({ children }: HeaderProps) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <>
            <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 dark:bg-gray-900 dark:border-gray-800">
                <button
                    type="button"
                    className="-m-2.5 p-2.5 text-gray-700 lg:hidden dark:text-gray-300"
                    onClick={() => setSidebarOpen(true)}
                >
                    <span className="sr-only">Open sidebar</span>
                    <Menu className="h-6 w-6" />
                </button>

                <div className="h-6 w-px bg-gray-200 lg:hidden dark:bg-gray-800" />

                <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div className="flex items-center gap-x-4 lg:gap-x-6">
                        <h1 className="text-lg font-semibold text-gray-900 dark:text-white md:hidden">Music Quiz</h1>
                    </div>
                    <div className="flex items-center gap-x-4 lg:gap-x-6 ml-auto">
                        {children}
                    </div>
                </div>
            </div>

            {/* Mobile sidebar overlay */}
            {sidebarOpen && (
                <div className="relative z-50 lg:hidden">
                    <div className="fixed inset-0 bg-gray-900/80" onClick={() => setSidebarOpen(false)} />
                    <div className="fixed inset-0 flex">
                        <div className="relative mr-16 flex w-full max-w-xs flex-1">
                            <div className="absolute left-full top-0 flex w-16 justify-center pt-5">
                                <button
                                    type="button"
                                    className="-m-2.5 p-2.5"
                                    onClick={() => setSidebarOpen(false)}
                                >
                                    <span className="sr-only">Close sidebar</span>
                                    <X className="h-6 w-6 text-white" />
                                </button>
                            </div>

                            <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 dark:bg-gray-900">
                                <div className="flex h-16 shrink-0 items-center">
                                    <h1 className="text-xl font-bold text-gray-900 dark:text-white">Music Quiz</h1>
                                </div>
                                <nav className="flex flex-1 flex-col">
                                    <ul role="list" className="flex flex-1 flex-col gap-y-7">
                                        <li>
                                            <ul role="list" className="-mx-2 space-y-1">
                                                <li>
                                                    <a
                                                        href="/"
                                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800"
                                                        onClick={() => setSidebarOpen(false)}
                                                    >
                                                        <span>Home</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a
                                                        href="/profile"
                                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800"
                                                        onClick={() => setSidebarOpen(false)}
                                                    >
                                                        <span>Profile</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a
                                                        href="/preferences"
                                                        className="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-gray-700 hover:text-indigo-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800"
                                                        onClick={() => setSidebarOpen(false)}
                                                    >
                                                        <span>Preferences</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
