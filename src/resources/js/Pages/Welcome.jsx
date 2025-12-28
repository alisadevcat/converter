import { Head, Link } from '@inertiajs/react';
import SimpleLayout from '@/Layouts/SimpleLayout';

export default function Welcome() {
    return (
        <SimpleLayout>
            <Head title="Welcome" />

            <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                        Currency Converter
                    </h1>
                    <p className="mt-6 text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
                        Convert currencies quickly and easily with real-time exchange rates.
                        Our currency converter supports 33+ currencies and provides up-to-date
                        exchange rates updated daily.
                    </p>
                    <div className="mt-10 flex items-center justify-center gap-x-6">
                        <Link
                            href={route('converter.index')}
                            className="rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Start Converting
                        </Link>
                        <Link
                            href={route('login')}
                            className="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700"
                        >
                            Learn more <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </div>

                {/* Features Section */}
                <div className="mt-24">
                    <div className="grid grid-cols-1 gap-8 sm:grid-cols-3">
                        <div className="text-center">
                            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg
                                    className="h-6 w-6 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mt-4 text-lg font-semibold text-gray-900">
                                33+ Currencies
                            </h3>
                            <p className="mt-2 text-sm text-gray-600">
                                Support for major world currencies including USD, EUR, GBP, and more.
                            </p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg
                                    className="h-6 w-6 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mt-4 text-lg font-semibold text-gray-900">
                                Real-Time Rates
                            </h3>
                            <p className="mt-2 text-sm text-gray-600">
                                Exchange rates updated daily from reliable sources.
                            </p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg
                                    className="h-6 w-6 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mt-4 text-lg font-semibold text-gray-900">
                                Easy to Use
                            </h3>
                            <p className="mt-2 text-sm text-gray-600">
                                Simple and intuitive interface for quick currency conversions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </SimpleLayout>
    );
}
