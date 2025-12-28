<?php

namespace Modules\Currency\Repositories;

use Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Modules\Currency\Models\ExchangeRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    /**
     * Find exchange rate by base currency, target currency, and date.
     *
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @param string|null $date
     * @return ExchangeRate|null
     */
    public function findByBaseAndTarget(string $baseCurrency, string $targetCurrency, ?string $date = null): ?ExchangeRate
    {
        $query = ExchangeRate::where('base_code', $baseCurrency)
            ->where('target_code', $targetCurrency);

        if ($date) {
            $query->where('date', $date);
        } else {
            // Get the latest rate for this pair
            $query->orderBy('date', 'desc');
        }

        return $query->first();
    }

    /**
     * Get all exchange rates.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return ExchangeRate::orderBy('date', 'desc')
            ->orderBy('base_code')
            ->orderBy('target_code')
            ->get();
    }

    /**
     * Get exchange rates by base currency.
     *
     * @param string $baseCurrency
     * @param string|null $date
     * @return Collection
     */
    public function getByBaseCurrency(string $baseCurrency, ?string $date = null): Collection
    {
        $query = ExchangeRate::where('base_code', $baseCurrency);

        if ($date) {
            $query->where('date', $date);
        } else {
            // Get latest rates for each target currency
            $subQuery = ExchangeRate::select('target_code', DB::raw('MAX(date) as max_date'))
                ->where('base_code', $baseCurrency)
                ->groupBy('target_code');

            $query->joinSub($subQuery, 'latest', function ($join) {
                $join->on('exchange_rate.target_code', '=', 'latest.target_code')
                    ->on('exchange_rate.date', '=', 'latest.max_date');
            });
        }

        return $query->orderBy('target_code')->get();
    }

    /**
     * Create or update an exchange rate.
     *
     * @param array $attributes
     * @param array $values
     * @return ExchangeRate
     */
    public function updateOrCreate(array $attributes, array $values): ExchangeRate
    {
        return ExchangeRate::updateOrCreate($attributes, $values);
    }

    /**
     * Create multiple exchange rates.
     *
     * @param array $rates
     * @return void
     */
    public function createMany(array $rates): void
    {
        ExchangeRate::insert($rates);
    }
}

