<?php

namespace App\Modules\Currency\DTOs;

use Illuminate\Contracts\Support\Arrayable;

class CurrencyConversionResult implements Arrayable
{
    private const AMOUNT_DECIMALS = 2;
    private const RATE_DECIMALS = 8;

    public function __construct(
        public readonly string $amount,
        public readonly string $fromCurrency,
        public readonly string $toCurrency,
        public readonly string $convertedAmount,
        public readonly string $rate,
    ) {}

    /**
     * Create from raw values.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $convertedAmount
     * @param float $rate
     * @return self
     */
    public static function fromValues(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        float $convertedAmount,
        float $rate
    ): self {
        return new self(
            amount: self::formatAmount($amount),
            fromCurrency: $fromCurrency,
            toCurrency: $toCurrency,
            convertedAmount: self::formatAmount($convertedAmount),
            rate: self::formatRate($rate),
        );
    }

    /**
     * Create for same currency conversion (1:1).
     *
     * @param float $amount
     * @param string $currency
     * @return self
     */
    public static function sameCurrency(float $amount, string $currency): self
    {
        return self::fromValues(
            $amount,
            $currency,
            $currency,
            $amount,
            1.0
        );
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'converted_amount' => $this->convertedAmount,
            'rate' => $this->rate,
        ];
    }

    /**
     * Format amount with 2 decimal places.
     */
    private static function formatAmount(float $value): string
    {
        return number_format($value, self::AMOUNT_DECIMALS, '.', '');
    }

    /**
     * Format rate with 8 decimal places.
     */
    private static function formatRate(float $value): string
    {
        return number_format($value, self::RATE_DECIMALS, '.', '');
    }
}
