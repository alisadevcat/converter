import { Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function SimpleLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            {/* Header with Navigation */}
            <header className="bg-white shadow-sm">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 items-center justify-between">
                        {/* Logo */}
                        <div className="flex items-center">
                            <Link href="/" className="flex items-center">
                                <ApplicationLogo className="h-8 w-8 fill-current text-gray-800" />
                                <span className="ml-2 text-xl font-semibold text-gray-800">
                                    Currency Converter
                                </span>
                            </Link>
                        </div>

                        {/* Navigation */}
                        <nav className="flex items-center space-x-4">
                            <Link
                                href="/"
                                className="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                            >
                                Home
                            </Link>
                            <Link
                                href={route('converter.index')}
                                className="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                            >
                                Converter
                            </Link>
                            <Link
                                href={route('login')}
                                className="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900"
                            >
                                Login
                            </Link>
                            <Link
                                href={route('register')}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                            >
                                Register
                            </Link>
                        </nav>
                    </div>
                </div>
            </header>

            {/* Main Content Area */}
            <main className="flex-1">
                {children}
            </main>

            {/* Footer */}
            <footer className="bg-white border-t border-gray-200">
                <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <div className="flex flex-col items-center justify-between space-y-4 sm:flex-row sm:space-y-0">
                        <p className="text-sm text-gray-600">
                            © {new Date().getFullYear()} Currency Converter. All rights reserved.
                        </p>
                        <div className="flex space-x-6">
                            <a
                                href="#"
                                className="text-sm text-gray-600 transition hover:text-gray-900"
                            >
                                Privacy
                            </a>
                            <a
                                href="#"
                                className="text-sm text-gray-600 transition hover:text-gray-900"
                            >
                                Terms
                            </a>
                            <a
                                href="#"
                                className="text-sm text-gray-600 transition hover:text-gray-900"
                            >
                                Contact
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}

