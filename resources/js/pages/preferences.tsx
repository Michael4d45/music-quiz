import Layout from "@/Layout";
import AppearanceToggleTab from "@/components/AppearanceToggleTab";

export default function Preferences() {
    return (
        <div className="max-w-4xl mx-auto">
            <div className="bg-white shadow sm:rounded-lg dark:bg-gray-800">
                <div className="px-4 py-5 sm:p-6">
                    <h3 className="text-lg font-medium leading-6 text-gray-900 dark:text-white">Preferences</h3>
                    <div className="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                        <p>Customize your application preferences.</p>
                    </div>
                </div>

                <div className="border-t border-gray-200 dark:border-gray-700">
                    <div className="px-4 py-5 sm:p-6">
                        <div className="space-y-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Theme
                                </label>
                                <AppearanceToggleTab />
                                <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Choose your preferred theme for the application.
                                </p>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Notifications
                                </label>
                                <div className="space-y-3">
                                    <div className="flex items-center">
                                        <input
                                            id="email-notifications"
                                            name="email-notifications"
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                            defaultChecked
                                        />
                                        <label htmlFor="email-notifications" className="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                                            Email notifications
                                        </label>
                                    </div>
                                    <div className="flex items-center">
                                        <input
                                            id="push-notifications"
                                            name="push-notifications"
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <label htmlFor="push-notifications" className="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                                            Push notifications
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Game Settings
                                </label>
                                <div className="space-y-3">
                                    <div className="flex items-center">
                                        <input
                                            id="sound-effects"
                                            name="sound-effects"
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                            defaultChecked
                                        />
                                        <label htmlFor="sound-effects" className="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                                            Sound effects
                                        </label>
                                    </div>
                                    <div className="flex items-center">
                                        <input
                                            id="background-music"
                                            name="background-music"
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <label htmlFor="background-music" className="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                                            Background music
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="px-4 py-3 bg-gray-50 text-right sm:px-6 dark:bg-gray-700">
                        <button
                            type="submit"
                            className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Save Preferences
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

Preferences.layout = (page: React.ReactNode) => <Layout>{page}</Layout>;
