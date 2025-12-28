<?php

namespace Modules\Currency\DTOs;

/**
 * Data Transfer Object for currency conversion results.
 */
class ConversionResult
{
    public function __construct(
        public readonly float $originalAmount,
        public readonly string $fromCurrency,
        public readonly string $toCurrency,
        public readonly float $convertedAmount,
        public readonly float $exchangeRate,
        public readonly ?string $rateDate = null,
        public readonly bool $isDirectRate = true,
        public readonly ?string $intermediateCurrency = null
    ) {
    }

    /**
     * Get formatted original amount.
     *
     * @param int $decimals
     * @return string
     */
    public function getFormattedOriginalAmount(int $decimals = 2): string
    {
        return number_format($this->originalAmount, $decimals, '.', ',');
    }

    /**
     * Get formatted converted amount.
     *
     * @param int $decimals
     * @return string
     */
    public function getFormattedConvertedAmount(int $decimals = 2): string
    {
        return number_format($this->convertedAmount, $decimals, '.', ',');
    }

    /**
     * Get formatted exchange rate.
     *
     * @param int $decimals
     * @return string
     */
    public function getFormattedExchangeRate(int $decimals = 4): string
    {
        return number_format($this->exchangeRate, $decimals, '.', ',');
    }

    /**
     * Convert to array for API responses.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'original_amount' => $this->originalAmount,
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'converted_amount' => $this->convertedAmount,
            'exchange_rate' => $this->exchangeRate,
            'rate_date' => $this->rateDate,
            'is_direct_rate' => $this->isDirectRate,
            'intermediate_currency' => $this->intermediateCurrency,
        ];
    }
}

