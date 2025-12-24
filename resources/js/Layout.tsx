import { usePage } from "@inertiajs/react";
import { useFlashToast } from "@/hooks/use-flash-toast";
import { Toaster } from "react-hot-toast";
import Sidebar from "@/components/Sidebar";
import Header from "@/components/Header";
import UserDropdown from "@/components/UserDropdown";
import AuthButtons from "@/components/AuthButtons";

export default function Layout({ children }: { children: React.ReactNode }) {
    useFlashToast();
    const { auth: { user } } = usePage<Props.SharedProps>().props;
    const loggedIn = !!user;

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <Toaster
                position="top-right"
                toastOptions={{
                    duration: 4000,
                    style: {
                        background: 'var(--toast-bg, #fff)',
                        color: 'var(--toast-text, #4f46e5)',
                        marginTop: '6vh',
                        border: '2px solid var(--toast-border, rgba(0, 0, 0, 0.06))',
                    },
                    success: {
                        iconTheme: {
                            primary: '#10b981',
                            secondary: '#fff',
                        },
                    },
                    error: {
                        iconTheme: {
                            primary: '#ef4444',
                            secondary: '#fff',
                        },
                    },
                }}
            />

            {/* Desktop sidebar */}
            <Sidebar />

            {/* Mobile header */}
            <Header>
                {loggedIn ? <UserDropdown user={user} /> : <AuthButtons />}
            </Header>

            {/* Main content */}
            <main className="md:pl-64">
                <div className="px-4 py-8 sm:px-6 lg:px-8">
                    {children}
                </div>
            </main>
        </div>
    );
}
