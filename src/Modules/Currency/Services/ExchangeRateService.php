<?php

namespace App\Modules\Currency\Services;

use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\Repositories\ExchangeRateRepository;
use Illuminate\Support\Collection;

class ExchangeRateService
{
    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository
    ) {}

    /**
     * Get latest exchange rates by base currency code.
     *
     * @param string $baseCurrencyCode The code of the base currency
     * @return Collection Collection of ExchangeRate models for the specified base currency
     */
    public function getLatestRatesByBaseCurrencyCode(string $baseCurrencyCode): Collection
    {
        return $this->exchangeRateRepository->getLatestRatesByBaseCurrencyCode($baseCurrencyCode);
    }

    // Note: This service can be enhanced in the future with additional business logic such as:
    // - Filtering inactive currencies
    // - Rate validation and formatting
    // - Date range filtering
    // - Rate history calculations
    // - etc.
}
