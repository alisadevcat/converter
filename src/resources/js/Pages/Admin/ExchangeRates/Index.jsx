import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ rates, currencies, selectedBaseCurrency, defaultBaseCurrency }) {
    const [baseCurrency, setBaseCurrency] = useState(selectedBaseCurrency || defaultBaseCurrency);

    const handleCurrencyChange = (e) => {
        const newCurrency = e.target.value;
        setBaseCurrency(newCurrency);
        router.visit(route('admin.exchange-rates.show', newCurrency), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Exchange Rates
                </h2>
            }
        >
            <Head title="Exchange Rates" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Currency Select */}
                            <div className="mb-6">
                                <label
                                    htmlFor="base-currency"
                                    className="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Base Currency
                                </label>
                                <select
                                    id="base-currency"
                                    value={baseCurrency}
                                    onChange={handleCurrencyChange}
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    {currencies.map((currency) => (
                                        <option key={currency.code} value={currency.code}>
                                            {currency.code} - {currency.name} ({currency.symbol})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Exchange Rates Table */}
                            <div className="overflow-x-auto">
                                {rates.length > 0 ? (
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th
                                                    scope="col"
                                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                >
                                                    Base
                                                </th>
                                                <th
                                                    scope="col"
                                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                >
                                                    Target
                                                </th>
                                                <th
                                                    scope="col"
                                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                >
                                                    Rate
                                                </th>
                                                <th
                                                    scope="col"
                                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                >
                                                    Date
                                                </th>
                                                <th
                                                    scope="col"
                                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                >
                                                    Updated At
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {rates.map((rate) => (
                                                <tr key={rate.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {rate.base}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {rate.target}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {rate.rate.toLocaleString('en-US', {
                                                            minimumFractionDigits: 4,
                                                            maximumFractionDigits: 8,
                                                        })}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {rate.date}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {rate.updated_at}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                ) : (
                                    <div className="text-center py-12">
                                        <p className="text-gray-500">
                                            No exchange rates found for base currency{' '}
                                            <span className="font-medium">{baseCurrency}</span>.
                                        </p>
                                        <p className="text-sm text-gray-400 mt-2">
                                            Rates will appear here after the daily sync completes.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

