<?php

namespace Modules\Currency\Services;

class CurrencyConfigService
{
    /**
     * Get all supported currencies.
     *
     * @return array
     */
    public function getCurrencies(): array
    {
        return config('currency.currencies', []);
    }

    /**
     * Get currency information by code.
     *
     * @param string $code
     * @return array|null
     */
    public function getCurrency(string $code): ?array
    {
        $currencies = $this->getCurrencies();
        return $currencies[strtoupper($code)] ?? null;
    }

    /**
     * Get currency name by code.
     *
     * @param string $code
     * @return string|null
     */
    public function getCurrencyName(string $code): ?string
    {
        $currency = $this->getCurrency($code);
        return $currency['name'] ?? null;
    }

    /**
     * Get currency symbol by code.
     *
     * @param string $code
     * @return string|null
     */
    public function getCurrencySymbol(string $code): ?string
    {
        $currency = $this->getCurrency($code);
        return $currency['symbol'] ?? null;
    }

    /**
     * Check if currency is supported.
     *
     * @param string $code
     * @return bool
     */
    public function isSupported(string $code): bool
    {
        return $this->getCurrency($code) !== null;
    }

    /**
     * Get all currency codes.
     *
     * @return array
     */
    public function getCurrencyCodes(): array
    {
        return array_keys($this->getCurrencies());
    }

    /**
     * Get default base currency.
     *
     * @return string
     */
    public function getDefaultBaseCurrency(): string
    {
        return config('currency.default_base_currency', 'USD');
    }
}

