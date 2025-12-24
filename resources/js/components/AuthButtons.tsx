import { Link } from '@inertiajs/react';

export default function AuthButtons() {
    return (
        <div className="flex items-center gap-x-3">
            <Link
                href="/login"
                className="text-sm font-semibold leading-6 text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
            >
                Log in
            </Link>
            <Link
                href="/register"
                className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Sign up
            </Link>
        </div>
    );
}
