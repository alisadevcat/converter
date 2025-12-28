<?php

namespace Modules\Currency\Contracts;

use Modules\Currency\DTOs\CurrencyConversionResult;
use Modules\Currency\Exceptions\CurrencyNotFoundException;
use Modules\Currency\Exceptions\CurrencyRateNotFoundException;
use Modules\Currency\Exceptions\InvalidAmountException;

interface CurrencyConverterInterface
{
    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount The amount to convert (must be positive)
     * @param string $fromCurrency The source currency code (e.g., 'USD')
     * @param string $toCurrency The target currency code (e.g., 'RUB')
     * @return CurrencyConversionResult The conversion result with details
     * @throws InvalidAmountException When amount is invalid (zero, negative, or NaN)
     * @throws CurrencyNotFoundException When currency is not supported
     * @throws CurrencyRateNotFoundException When exchange rate is not found
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): CurrencyConversionResult;
}

