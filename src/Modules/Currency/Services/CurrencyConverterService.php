<?php

namespace Modules\Currency\Services;

use Modules\Currency\Contracts\CurrencyConverterInterface;
use Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Modules\Currency\Exceptions\CurrencyNotFoundException;
use Modules\Currency\Exceptions\CurrencyRateNotFoundException;

class CurrencyConverterService implements CurrencyConverterInterface
{
    public function __construct(
        protected ExchangeRateRepositoryInterface $repository,
        protected CurrencyConfigService $configService
    ) {
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param string|null $date
     * @return float
     * @throws CurrencyNotFoundException
     * @throws CurrencyRateNotFoundException
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency, ?string $date = null): float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        // Validate currencies
        if (!$this->configService->isSupported($fromCurrency)) {
            throw CurrencyNotFoundException::forCurrency($fromCurrency);
        }

        if (!$this->configService->isSupported($toCurrency)) {
            throw CurrencyNotFoundException::forCurrency($toCurrency);
        }

        // If same currency, return amount as is
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Get exchange rate
        $rate = $this->repository->findByBaseAndTarget($fromCurrency, $toCurrency, $date);

        if (!$rate) {
            throw CurrencyRateNotFoundException::forRate($fromCurrency, $toCurrency, $date);
        }

        return $amount * $rate->rate;
    }
}

