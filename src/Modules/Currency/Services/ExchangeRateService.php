<?php

namespace Modules\Currency\Services;

use Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Modules\Currency\Models\ExchangeRate;
use Illuminate\Support\Collection;

class ExchangeRateService
{
    public function __construct(
        protected ExchangeRateRepositoryInterface $repository,
        protected CurrencyConfigService $configService
    ) {
    }

    /**
     * Get all exchange rates.
     *
     * @return Collection
     */
    public function getAllRates(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get exchange rates by base currency.
     *
     * @param string $baseCurrency
     * @param string|null $date
     * @return Collection
     */
    public function getRatesByBaseCurrency(string $baseCurrency, ?string $date = null): Collection
    {
        $baseCurrency = strtoupper($baseCurrency);

        if (!$this->configService->isSupported($baseCurrency)) {
            return collect();
        }

        return $this->repository->getByBaseCurrency($baseCurrency, $date);
    }

    /**
     * Get exchange rate for specific currencies.
     *
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @param string|null $date
     * @return ExchangeRate|null
     */
    public function getRate(string $baseCurrency, string $targetCurrency, ?string $date = null): ?ExchangeRate
    {
        $baseCurrency = strtoupper($baseCurrency);
        $targetCurrency = strtoupper($targetCurrency);

        return $this->repository->findByBaseAndTarget($baseCurrency, $targetCurrency, $date);
    }
}

