<?php

namespace Modules\Currency\Repositories\Converter;

use Modules\Currency\Contracts\Converter\CurrencyRateLookupInterface;
use Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Modules\Currency\Models\ExchangeRate;

class CurrencyRateLookupRepository implements CurrencyRateLookupInterface
{
    public function __construct(
        protected ExchangeRateRepositoryInterface $exchangeRateRepository
    ) {
    }

    /**
     * Find direct exchange rate between two currencies.
     *
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @param string|null $date
     * @return ExchangeRate|null
     */
    public function findDirectRate(string $baseCurrency, string $targetCurrency, ?string $date = null): ?ExchangeRate
    {
        return $this->exchangeRateRepository->findByBaseAndTarget($baseCurrency, $targetCurrency, $date);
    }

    /**
     * Find exchange rate via intermediate currency (e.g., USD).
     *
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @param string $intermediateCurrency
     * @param string|null $date
     * @return array{base_to_intermediate: ExchangeRate|null, intermediate_to_target: ExchangeRate|null}
     */
    public function findRateViaIntermediate(
        string $baseCurrency,
        string $targetCurrency,
        string $intermediateCurrency,
        ?string $date = null
    ): array {
        $baseToIntermediate = $this->exchangeRateRepository->findByBaseAndTarget(
            $baseCurrency,
            $intermediateCurrency,
            $date
        );

        $intermediateToTarget = $this->exchangeRateRepository->findByBaseAndTarget(
            $intermediateCurrency,
            $targetCurrency,
            $date
        );

        return [
            'base_to_intermediate' => $baseToIntermediate,
            'intermediate_to_target' => $intermediateToTarget,
        ];
    }
}

