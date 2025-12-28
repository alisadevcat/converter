/**
 * Currency Utilities
 *
 * Helper functions for currency handling in the frontend.
 * The backend always sends currencies as a simple array of currency codes: ['USD', 'EUR', 'RUB']
 */

import { DEFAULT_FROM_CURRENCY, DEFAULT_TO_CURRENCY } from './constants';

/**
 * Get a valid alternative currency for "to" field
 *
 * @param {string[]} availableCurrencies - Array of available currency codes
 * @param {string} fromCurrency - The selected "from" currency code
 * @param {string} defaultToCurrency - Default currency to prefer for "to" field
 * @returns {string} A valid currency code for the "to" field
 */
export function getValidToCurrency(availableCurrencies, fromCurrency, defaultToCurrency = DEFAULT_TO_CURRENCY) {
    const alternatives = availableCurrencies.filter(code => code !== fromCurrency);

    // Prefer default "to" currency if available and different from "from"
    if (availableCurrencies.includes(defaultToCurrency) && fromCurrency !== defaultToCurrency) {
        return defaultToCurrency;
    }

    // Otherwise use first available alternative
    return alternatives[0] || availableCurrencies[0];
}

/**
 * Validate and correct currency selections when available currencies change
 *
 * @param {string[]} availableCurrencies - Array of available currency codes
 * @param {string} currentFrom - Currently selected "from" currency
 * @param {string} currentTo - Currently selected "to" currency
 * @param {Function} setData - Function to update form data
 * @param {string} defaultFromCurrency - Default currency for "from" field
 * @param {string} defaultToCurrency - Default currency for "to" field
 */
export function validateCurrencySelection(
    availableCurrencies,
    currentFrom,
    currentTo,
    setData,
    defaultFromCurrency = DEFAULT_FROM_CURRENCY,
    defaultToCurrency = DEFAULT_TO_CURRENCY
) {
    if (availableCurrencies.length === 0) return;

    const isFromValid = availableCurrencies.includes(currentFrom);
    const isToValid = availableCurrencies.includes(currentTo);

    // If both are valid, no changes needed
    if (isFromValid && isToValid) return;

    // Calculate corrected values
    const correctedFrom = isFromValid
        ? currentFrom
        : (availableCurrencies.includes(defaultFromCurrency)
            ? defaultFromCurrency
            : availableCurrencies[0]);

    const correctedTo = isToValid
        ? currentTo
        : getValidToCurrency(availableCurrencies, correctedFrom, defaultToCurrency);

    // Apply updates only if needed
    const updates = {};
    if (!isFromValid) updates.from_currency = correctedFrom;
    if (!isToValid) updates.to_currency = correctedTo;

    if (Object.keys(updates).length > 0) {
        setData(updates);
    }
}

