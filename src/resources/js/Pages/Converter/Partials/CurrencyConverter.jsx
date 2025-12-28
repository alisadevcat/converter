import { useForm, router } from '@inertiajs/react';
import { useEffect } from 'react';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import Select from '@/Components/Select';
import { validateCurrencySelection } from '@/utils/currencyUtils';
import { DEFAULT_CURRENCIES, DEFAULT_FROM_CURRENCY, DEFAULT_TO_CURRENCY } from '@/utils/constants';

export default function CurrencyConverter({ currencies = [], result = null, error = null }) {
    const currenciesArray = Array.isArray(currencies) ? currencies.filter(Boolean).sort() : [];
    const availableCurrencies = currenciesArray.length > 0 ? currenciesArray : DEFAULT_CURRENCIES;

    const { data, setData, processing, errors } = useForm({
        amount: '',
        from_currency: DEFAULT_FROM_CURRENCY,
        to_currency: DEFAULT_TO_CURRENCY,
    }, {
        remember: 'currency-converter-form',
    });

    // Validate currency selections when currencies or selections change
    useEffect(() => {
        validateCurrencySelection(
            availableCurrencies,
            data.from_currency,
            data.to_currency,
            setData
        );
    }, [availableCurrencies, data.from_currency, data.to_currency, setData]);

    const handleSubmit = (e) => {
        e.preventDefault();
        router.post(route('converter.convert'), data, {
            preserveScroll: true,
            preserveState: true,
            preserveUrl: true,
        });
    };

    const renderCurrencyOptions = (options) =>
        options.map(code => (
            <option key={code} value={code}>{code}</option>
        ));

    return (
        <div className="rounded-lg bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05]">
            <h2 className="text-2xl font-semibold text-black mb-4">
                Currency Converter
            </h2>
            <p className="text-gray-600 mb-6">
                Convert between different currencies using real-time exchange rates
            </p>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Amount Input */}
                <div>
                    <InputLabel htmlFor="amount" value="Amount" />
                    <TextInput
                        id="amount"
                        type="number"
                        value={data.amount}
                        onChange={(e) => setData('amount', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder="Enter amount"
                        step="0.01"
                        min="0"
                        required
                    />
                    <InputError message={errors.amount} className="mt-2" />
                </div>

                {/* Currency Selectors */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel htmlFor="from_currency" value="From Currency" />
                        <Select
                            id="from_currency"
                            value={data.from_currency}
                            onChange={(e) => setData('from_currency', e.target.value)}
                            required
                        >
                            {renderCurrencyOptions(availableCurrencies)}
                        </Select>
                        <InputError message={errors.from_currency} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="to_currency" value="To Currency" />
                        <Select
                            id="to_currency"
                            value={data.to_currency}
                            onChange={(e) => setData('to_currency', e.target.value)}
                            required
                        >
                            {renderCurrencyOptions(availableCurrencies)}
                        </Select>
                        <InputError message={errors.to_currency} className="mt-2" />
                    </div>
                </div>

                {/* Submit Button */}
                <div>
                    <PrimaryButton type="submit" disabled={processing} className="w-full">
                        {processing ? 'Converting...' : 'Convert'}
                    </PrimaryButton>
                </div>
            </form>

            {/* Conversion Result */}
            {result && (
                <div className="mt-8 p-6 bg-green-50 border border-green-200 rounded-lg">
                    <div className="text-sm text-green-600 mb-2">Conversion Result</div>
                    <div className="text-3xl font-bold text-green-700">
                        {result.amount} {result.from_currency} = {result.converted_amount} {result.to_currency}
                    </div>
                    {result.rate && (
                        <div className="mt-2 text-sm text-green-600">
                            Exchange Rate: 1 {result.from_currency} = {result.rate} {result.to_currency}
                        </div>
                    )}
                </div>
            )}

            {/* Error Message */}
            {error && (
                <div className="mt-8 p-6 bg-red-50 border border-red-200 rounded-lg">
                    <div className="text-sm font-medium text-red-800">{error}</div>
                </div>
            )}
        </div>
    );
}

