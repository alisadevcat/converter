import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import ChooseCurrency from "@/Pages/Admin/ExchangeRates/Partials/ChooseCurrency";
import ExchangeRatesTable from "@/Pages/Admin/ExchangeRates/Partials/ExchangeRatesTable";

export default function ExchangeRates({ exchangeRates = {}, flash, currencies }) {
    // ExchangeRateResource::collection() returns data wrapped in 'data' property
    const ratesArray = exchangeRates?.data || exchangeRates || [];
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Exchange Rates Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                    {/* Flash Messages */}
                    {flash?.success && (
                        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-200" role="alert">
                            <span className="block sm:inline">{flash.success}</span>
                        </div>
                    )}
                    {flash?.error && (
                        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-200" role="alert">
                            <span className="block sm:inline">{flash.error}</span>
                        </div>
                    )}

                    {/* Exchange Rates Content */}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <ChooseCurrency currencies={currencies} />
                            <ExchangeRatesTable exchangeRates={ratesArray} />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
