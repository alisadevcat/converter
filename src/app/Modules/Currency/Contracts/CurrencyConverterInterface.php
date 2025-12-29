<?php

namespace App\Modules\Currency\Contracts;

use App\Modules\Currency\DTOs\CurrencyConversionResult;

interface CurrencyConverterInterface
{
    /**
     * Convert currency amount from one currency to another.
     *
     * @param float $amount The amount to convert
     * @param string $fromCurrency The source currency code
     * @param string $toCurrency The target currency code
     * @return CurrencyConversionResult The conversion result with formatted values
     * @throws \InvalidArgumentException If currency codes are invalid
     * @throws \RuntimeException If exchange rate is not found
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): CurrencyConversionResult;
}

