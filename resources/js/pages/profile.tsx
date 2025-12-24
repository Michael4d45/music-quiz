import { usePage } from "@inertiajs/react";
import Layout from "@/Layout";

export default function Profile() {
    const { auth: { user } } = usePage<Props.SharedProps>().props;

    return (
        <div className="max-w-4xl mx-auto">
            <div className="bg-white shadow sm:rounded-lg dark:bg-gray-800">
                <div className="px-4 py-5 sm:p-6">
                    <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-white">Profile Information</h3>
                    <div className="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                        <p>Update your account's profile information and email address.</p>
                    </div>
                </div>

                <div className="border-t border-gray-200 dark:border-gray-700">
                    <div className="px-4 py-5 sm:p-6">
                        <div className="grid grid-cols-6 gap-6">
                            <div className="col-span-6 sm:col-span-4">
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Name
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    defaultValue={user?.name}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div className="col-span-6 sm:col-span-4">
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    defaultValue={user?.email}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <div className="px-4 py-3 bg-gray-50 text-right sm:px-6 dark:bg-gray-700">
                        <button
                            type="submit"
                            className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

Profile.layout = (page: React.ReactNode) => <Layout>{page}</Layout>;
