<?php

namespace Modules\Currency\Contracts;

interface CurrencyConverterInterface
{
    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount The amount to convert
     * @param string $fromCurrency The source currency code (e.g., 'USD')
     * @param string $toCurrency The target currency code (e.g., 'RUB')
     * @param string|null $date Optional date for historical rates (Y-m-d format). If null, uses latest rate.
     * @return float The converted amount
     * @throws \Modules\Currency\Exceptions\CurrencyNotFoundException
     * @throws \Modules\Currency\Exceptions\CurrencyRateNotFoundException
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency, ?string $date = null): float;
}

