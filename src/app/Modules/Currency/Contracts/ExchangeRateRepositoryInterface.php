<?php

namespace App\Modules\Currency\Contracts;

use App\Modules\Currency\Models\ExchangeRate;
use Illuminate\Support\Collection;

interface ExchangeRateRepositoryInterface
{
    /**
     * Get latest exchange rates by base currency code.
     *
     * @param string $baseCurrencyCode The code of the base currency
     * @return Collection Collection of ExchangeRate models ordered by date desc, then target_code
     */
    public function getLatestRatesByBaseCurrencyCode(string $baseCurrencyCode): Collection;



    /**
     * Find exchange rate by base currency, target currency, and date.
     *
     * @param string $baseCurrencyCode The code of the base currency
     * @param string $targetCurrencyCode The code of the target currency
     * @param string $date Date string in 'Y-m-d' format
     * @return ExchangeRate|null The exchange rate or null if not found
     */
    public function findRate(string $baseCurrencyCode, string $targetCurrencyCode, string $date): ?ExchangeRate;

    /**
     * Find the latest available exchange rate (fallback when specific date not found).
     *
     * @param string $baseCurrencyCode The base currency code
     * @param string $targetCurrencyCode The target currency code
     * @return ExchangeRate|null The latest exchange rate or null if not found
     */
    public function findLatestRate(string $baseCurrencyCode, string $targetCurrencyCode): ?ExchangeRate;

    /**
     * Check if exchange rates exist for a specific base currency and date.
     *
     * @param string $baseCurrencyCode The base currency code
     * @param string $date Date string in 'Y-m-d' format
     * @return bool True if rates exist, false otherwise
     */
    public function hasRatesForDate(string $baseCurrencyCode, string $date): bool;

    /**
     * Get all base currency codes that have rates for a specific date.
     *
     * @param string $date Date string in 'Y-m-d' format
     * @return array Array of base currency codes
     */
    public function getBaseCurrencyCodesWithRatesForDate(string $date): array;

    /**
     * Upsert daily exchange rates for a base currency.
     * Creates new records or updates existing ones based on unique constraint.
     *
     * @param string $baseCurrencyCode The code of the base currency
     * @param array $rates Array of rates in format ['target_code' => rate, ...]
     * @param string $date Date string in 'Y-m-d' format
     * @return void
     */
    public function upsertDailyRates(
        string $baseCurrencyCode,
        array $rates,
        string $date
    ): void;
}
