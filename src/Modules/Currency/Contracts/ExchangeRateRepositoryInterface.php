<?php

namespace Modules\Currency\Contracts;

use Modules\Currency\Models\ExchangeRate;
use Illuminate\Support\Collection;

interface ExchangeRateRepositoryInterface
{
    /**
     * Find exchange rate by base currency, target currency, and date.
     *
     * @param string $baseCurrency The base currency code
     * @param string $targetCurrency The target currency code
     * @param string|null $date Date in Y-m-d format. If null, uses latest available rate.
     * @return ExchangeRate|null
     */
    public function findByBaseAndTarget(string $baseCurrency, string $targetCurrency, ?string $date = null): ?ExchangeRate;

    /**
     * Get all exchange rates.
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get exchange rates by base currency.
     *
     * @param string $baseCurrency The base currency code
     * @param string|null $date Optional date filter
     * @return Collection
     */
    public function getByBaseCurrency(string $baseCurrency, ?string $date = null): Collection;

    /**
     * Create or update an exchange rate.
     *
     * @param array $attributes Search attributes (base_code, target_code, date)
     * @param array $values Values to update/create
     * @return ExchangeRate
     */
    public function updateOrCreate(array $attributes, array $values): ExchangeRate;

    /**
     * Create multiple exchange rates.
     *
     * @param array $rates Array of rate data
     * @return void
     */
    public function createMany(array $rates): void;
}

