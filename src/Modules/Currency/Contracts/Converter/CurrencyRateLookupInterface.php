<?php

namespace Modules\Currency\Contracts\Converter;

use Modules\Currency\Models\ExchangeRate;

interface CurrencyRateLookupInterface
{
    /**
     * Find direct exchange rate between two currencies.
     *
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @param string|null $date
     * @return ExchangeRate|null
     */
    public function findDirectRate(string $baseCurrency, string $targetCurrency, ?string $date = null): ?ExchangeRate;

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
    ): array;
}

