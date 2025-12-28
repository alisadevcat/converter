import React, { useState, useEffect } from 'react';
import { router, usePage } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import Select from '@/Components/Select';
export default function ChooseCurrency({ currencies = [], defaultCurrency = 'USD' }) {
    // Ensure currencies is an array, filter falsy values, and sort
    const currencyCodes = Array.isArray(currencies) ? currencies.filter(Boolean).sort() : [];

    // Get current URL to extract currency code
    const { url } = usePage();

    // Extract currency code from URL (e.g., /exchange-rates/USD)
    const getCurrencyFromUrl = () => {
        const path = url || window.location.pathname;
        const match = path.match(/\/exchange-rates\/([A-Z]{3})$/);
        return match ? match[1] : null;
    };

    // Initialize with currency from URL, or defaultCurrency, or first available
    const getInitialCurrency = () => {
        const urlCurrency = getCurrencyFromUrl();
        if (urlCurrency && currencyCodes.includes(urlCurrency)) {
            return urlCurrency;
        }
        if (currencyCodes.length > 0) {
            return currencyCodes.includes(defaultCurrency)
                ? defaultCurrency
                : currencyCodes[0];
        }
        return defaultCurrency;
    };

    const [selectedCurrency, setSelectedCurrency] = useState(getInitialCurrency());

    // Update selected currency when URL changes (e.g., browser back/forward)
    useEffect(() => {
        const urlCurrency = getCurrencyFromUrl();
        if (urlCurrency && urlCurrency !== selectedCurrency && currencyCodes.includes(urlCurrency)) {
            setSelectedCurrency(urlCurrency);
        }
    }, [url, currencyCodes]);

    const handleChange = (e) => {
        const currencyCode = e.target.value;
        setSelectedCurrency(currencyCode);
        // Navigate to the new URL with the selected currency
        router.get(route('exchange-rates.show', currencyCode), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <div className="space-y-4">
            <div>
                <InputLabel htmlFor="currency_code" value="Select Base Currency" />
                <Select
                    id="currency_code"
                    name="base_currency"
                    value={selectedCurrency}
                    onChange={handleChange}
                >
                    {currencyCodes.map((code) => (
                        <option key={code} value={code}>
                            {code}
                        </option>
                    ))}
                </Select>
            </div>
        </div>
    );
}

