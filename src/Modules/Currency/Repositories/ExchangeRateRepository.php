<?php

namespace App\Modules\Currency\Repositories;

use App\Modules\Currency\Models\ExchangeRate;
use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Illuminate\Support\Collection;

class ExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    /**
     * Get latest exchange rates by base currency code.
     *
     * @param string $baseCurrencyCode The code of the base currency
     * @return Collection Collection of ExchangeRate models ordered by date desc, then target_code
     */
    public function getLatestRatesByBaseCurrencyCode(string $baseCurrencyCode): Collection
    {
        return ExchangeRate::where('base_code', $baseCurrencyCode)
            ->orderBy('date', 'desc')
            ->orderBy('target_code')
            ->get();
    }

    /**
     * Find exchange rate by base currency, target currency, and date.
     *
     * @param string $baseCurrencyCode The base currency code
     * @param string $targetCurrencyCode The target currency code
     * @param string $date Date string in 'Y-m-d' format
     * @return ExchangeRate|null The exchange rate or null if not found
     */
    public function findRate(string $baseCurrencyCode, string $targetCurrencyCode, string $date): ?ExchangeRate
    {
        return ExchangeRate::where('base_code', $baseCurrencyCode)
            ->where('target_code', $targetCurrencyCode)
            ->where('date', $date)
            ->first();
    }

    /**
     * Find the latest available exchange rate (fallback when specific date not found).
     *
     * @param string $baseCurrencyCode The base currency code
     * @param string $targetCurrencyCode The target currency code
     * @return ExchangeRate|null The latest exchange rate or null if not found
     */
    public function findLatestRate(string $baseCurrencyCode, string $targetCurrencyCode): ?ExchangeRate
    {
        return ExchangeRate::where('base_code', $baseCurrencyCode)
            ->where('target_code', $targetCurrencyCode)
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Check if exchange rates exist for a specific base currency and date.
     *
     * @param string $baseCurrencyCode The base currency code
     * @param string $date Date string in 'Y-m-d' format
     * @return bool True if rates exist, false otherwise
     */
    public function hasRatesForDate(string $baseCurrencyCode, string $date): bool
    {
        return ExchangeRate::where('base_code', $baseCurrencyCode)
            ->where('date', $date)
            ->exists();
    }

    /**
     * Get all base currency codes that have rates for a specific date.
     *
     * @param string $date Date string in 'Y-m-d' format
     * @return array Array of base currency codes
     */
    public function getBaseCurrencyCodesWithRatesForDate(string $date): array
    {
        return ExchangeRate::where('date', $date)
            ->distinct()
            ->pluck('base_code')
            ->toArray();
    }

    /**
     * Upsert daily exchange rates for a base currency.
     * Creates new records or updates existing ones based on unique constraint.
     *
     * @param string $baseCurrencyCode The base currency code
     * @param array $rates Array of rates in format ['target_code' => rate, ...]
     * @param string $date Date string in 'Y-m-d' format
     * @return void
     */
    public function upsertDailyRates(
        string $baseCurrencyCode,
        array $rates,
        string $date
    ): void {
        $now = now();
        $rows = [];

        foreach ($rates as $targetCurrencyCode => $rate) {
            // Skip if rate is not numeric
            if (!is_numeric($rate)) {
                continue;
            }

            $rows[] = [
                'base_code' => $baseCurrencyCode,
                'target_code' => $targetCurrencyCode,
                'rate' => (float) $rate,
                'date' => $date,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            return;
        }

        // Upsert with unique constraint on base_code, target_code, date
        ExchangeRate::upsert(
            $rows,
            ['base_code', 'target_code', 'date'],
            ['rate', 'updated_at']
        );
    }
}
