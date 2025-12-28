import SimpleLayout from '@/Layouts/SimpleLayout';
import { Head } from '@inertiajs/react';

export default function Index({ currencies, defaultBaseCurrency }) {
    return (
        <SimpleLayout>
            <Head title="Currency Converter" />

            <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Currency Converter
                    </h1>
                    <p className="mt-4 text-lg text-gray-600">
                        Convert between currencies using real-time exchange rates
                    </p>
                </div>

                <div className="mx-auto max-w-2xl">
                    <div className="bg-white rounded-lg shadow-lg p-8">
                        <p className="text-center text-gray-600">
                            Converter functionality will be implemented here.
                        </p>
                        <p className="text-center text-sm text-gray-500 mt-2">
                            Default base currency: {defaultBaseCurrency}
                        </p>
                    </div>
                </div>
            </div>
        </SimpleLayout>
    );
}

