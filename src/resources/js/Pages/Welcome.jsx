import { Head, Link } from '@inertiajs/react';
import SimpleLayout from '@/Layouts/SimpleLayout';

export default function Welcome({ laravelVersion, phpVersion }) {
    return (
        <SimpleLayout
            footer={`Laravel v${laravelVersion} (PHP v${phpVersion})`}
        >
            <Head title="Welcome" />
            <div className="flex flex-col items-center justify-center text-center">
                <div className="max-w-3xl mx-auto px-6">
                    <h1 className="text-4xl font-bold text-black mb-6">
                        Welcome to Currency Converter
                    </h1>
                    <p className="text-lg text-black/70 mb-8">
                        Convert currencies with real-time exchange rates.
                        Our system automatically updates exchange rates daily from reliable sources,
                        ensuring you always have access to the latest conversion rates.
                    </p>

                    {/* Currency Converter */}
                    <div className="mb-8">
                        <Link
                            href={route('converter.index')}
                            className="rounded-md px-3 py-2 text-black border ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]"
                        >
                            Converter
                        </Link>
                    </div>
                </div>
            </div>
        </SimpleLayout>
    );
}
