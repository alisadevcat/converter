<?php

namespace Modules\Currency\Services;

use Modules\Currency\Contracts\CurrencyConverterInterface;
use Modules\Currency\Contracts\Converter\CurrencyRateLookupInterface;
use Modules\Currency\DTOs\CurrencyConversionResult;
use Modules\Currency\Exceptions\CurrencyNotFoundException;
use Modules\Currency\Exceptions\CurrencyRateNotFoundException;
use Modules\Currency\Exceptions\InvalidAmountException;
use Illuminate\Support\Facades\Log;

class CurrencyConverterService implements CurrencyConverterInterface
{
    protected string $fallbackCurrency;
    protected bool $enableFallback;
    protected int $amountDecimals;
    protected int $rateDecimals;
    protected bool $roundResult;
    protected float $minAmount;
    protected float $maxAmount;

    public function __construct(
        protected CurrencyRateLookupInterface $rateLookup,
        protected CurrencyConfigService $configService
    ) {
        $this->fallbackCurrency = config('currency.conversion.fallback_currency', 'USD');
        $this->enableFallback = config('currency.conversion.enable_fallback', true);
        $this->amountDecimals = config('currency.conversion.amount_decimals', 2);
        $this->rateDecimals = config('currency.conversion.rate_decimals', 4);
        $this->roundResult = config('currency.conversion.round_result', true);
        $this->minAmount = config('currency.conversion.min_amount', 0.01);
        $this->maxAmount = config('currency.conversion.max_amount', 999999999.99);
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return CurrencyConversionResult
     * @throws InvalidAmountException
     * @throws CurrencyNotFoundException
     * @throws CurrencyRateNotFoundException
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): CurrencyConversionResult
    {
        // Normalize currency codes
        $fromCurrency = strtoupper(trim($fromCurrency));
        $toCurrency = strtoupper(trim($toCurrency));

        // Validate amount
        $this->validateAmount($amount);

        // Validate currencies
        $this->validateCurrency($fromCurrency);
        $this->validateCurrency($toCurrency);

        // Handle same currency - return 1:1 conversion
        if ($fromCurrency === $toCurrency) {
            return $this->createSameCurrencyResult($amount, $fromCurrency);
        }

        // Try direct lookup first
        $rate = $this->rateLookup->findDirectRate($fromCurrency, $toCurrency);

        if ($rate) {
            return $this->createDirectConversionResult($amount, $fromCurrency, $toCurrency, $rate);
        }

        // Fallback: Convert via intermediate currency (e.g., USD)
        if ($this->enableFallback && $this->canUseFallback($fromCurrency, $toCurrency)) {
            return $this->convertViaIntermediate($amount, $fromCurrency, $toCurrency);
        }

        // No rate found and fallback disabled or not applicable
        throw CurrencyRateNotFoundException::forRate($fromCurrency, $toCurrency);
    }

    /**
     * Create result for same currency conversion (1:1).
     *
     * @param float $amount
     * @param string $currency
     * @return CurrencyConversionResult
     */
    protected function createSameCurrencyResult(float $amount, string $currency): CurrencyConversionResult
    {
        return new CurrencyConversionResult(
            originalAmount: $amount,
            fromCurrency: $currency,
            toCurrency: $currency,
            convertedAmount: $this->formatAmount($amount),
            exchangeRate: 1.0,
            rateDate: null,
            isDirectRate: true,
            intermediateCurrency: null
        );
    }

    /**
     * Create result for direct conversion.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param \Modules\Currency\Models\ExchangeRate $rate
     * @return CurrencyConversionResult
     */
    protected function createDirectConversionResult(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        $rate
    ): CurrencyConversionResult {
        $convertedAmount = $amount * $rate->rate;
        $convertedAmount = $this->formatAmount($convertedAmount);

        return new CurrencyConversionResult(
            originalAmount: $amount,
            fromCurrency: $fromCurrency,
            toCurrency: $toCurrency,
            convertedAmount: $convertedAmount,
            exchangeRate: (float) $rate->rate,
            rateDate: $rate->date?->format('Y-m-d'),
            isDirectRate: true,
            intermediateCurrency: null
        );
    }

    /**
     * Convert via intermediate currency (fallback strategy).
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return CurrencyConversionResult
     * @throws CurrencyRateNotFoundException
     */
    protected function convertViaIntermediate(
        float $amount,
        string $fromCurrency,
        string $toCurrency
    ): CurrencyConversionResult {
        $rates = $this->rateLookup->findRateViaIntermediate(
            $fromCurrency,
            $toCurrency,
            $this->fallbackCurrency
        );

        $baseToIntermediate = $rates['base_to_intermediate'];
        $intermediateToTarget = $rates['intermediate_to_target'];

        // Check if both rates are available
        if (!$baseToIntermediate || !$intermediateToTarget) {
            $missingRates = [];
            if (!$baseToIntermediate) {
                $missingRates[] = "{$fromCurrency} → {$this->fallbackCurrency}";
            }
            if (!$intermediateToTarget) {
                $missingRates[] = "{$this->fallbackCurrency} → {$toCurrency}";
            }

            Log::warning('Currency conversion fallback failed - missing rates', [
                'from' => $fromCurrency,
                'to' => $toCurrency,
                'intermediate' => $this->fallbackCurrency,
                'missing_rates' => $missingRates,
            ]);

            throw CurrencyRateNotFoundException::forRate($fromCurrency, $toCurrency);
        }

        // Calculate conversion: fromCurrency → intermediate → toCurrency
        // Step 1: Convert to intermediate currency
        $intermediateAmount = $amount * $baseToIntermediate->rate;

        // Step 2: Convert from intermediate to target currency
        $convertedAmount = $intermediateAmount * $intermediateToTarget->rate;
        $convertedAmount = $this->formatAmount($convertedAmount);

        // Calculate effective rate
        $effectiveRate = $baseToIntermediate->rate * $intermediateToTarget->rate;

        // Use the most recent date from the two rates
        $rateDate = $this->getMostRecentDate($baseToIntermediate->date, $intermediateToTarget->date);

        Log::info('Currency conversion via intermediate currency', [
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'intermediate' => $this->fallbackCurrency,
            'effective_rate' => $effectiveRate,
        ]);

        return new CurrencyConversionResult(
            originalAmount: $amount,
            fromCurrency: $fromCurrency,
            toCurrency: $toCurrency,
            convertedAmount: $convertedAmount,
            exchangeRate: $effectiveRate,
            rateDate: $rateDate,
            isDirectRate: false,
            intermediateCurrency: $this->fallbackCurrency
        );
    }

    /**
     * Check if fallback can be used.
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return bool
     */
    protected function canUseFallback(string $fromCurrency, string $toCurrency): bool
    {
        return $this->fallbackCurrency !== $fromCurrency
            && $this->fallbackCurrency !== $toCurrency;
    }

    /**
     * Get the most recent date from two dates.
     *
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon|null $date1
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon|null $date2
     * @return string|null
     */
    protected function getMostRecentDate($date1, $date2): ?string
    {
        if (!$date1 && !$date2) {
            return null;
        }

        if (!$date1) {
            return $date2->format('Y-m-d');
        }

        if (!$date2) {
            return $date1->format('Y-m-d');
        }

        return max($date1->format('Y-m-d'), $date2->format('Y-m-d'));
    }

    /**
     * Validate that amount is valid for conversion.
     *
     * @param float $amount
     * @return void
     * @throws InvalidAmountException
     */
    protected function validateAmount(float $amount): void
    {
        // Check for NaN or Infinity
        if (!is_finite($amount)) {
            throw InvalidAmountException::forAmount($amount);
        }

        // Check for zero
        if ($amount == 0) {
            throw InvalidAmountException::forZeroAmount();
        }

        // Check for negative
        if ($amount < 0) {
            throw InvalidAmountException::forNegativeAmount($amount);
        }

        // Check minimum amount
        if ($amount < $this->minAmount) {
            throw new InvalidAmountException(
                "Amount must be at least {$this->minAmount}. Provided: {$amount}."
            );
        }

        // Check maximum amount
        if ($amount > $this->maxAmount) {
            throw new InvalidAmountException(
                "Amount exceeds maximum allowed value of {$this->maxAmount}. Provided: {$amount}."
            );
        }
    }

    /**
     * Validate that currency is supported.
     *
     * @param string $currency
     * @return void
     * @throws CurrencyNotFoundException
     */
    protected function validateCurrency(string $currency): void
    {
        if (empty($currency)) {
            throw new CurrencyNotFoundException("Currency code cannot be empty.");
        }

        if (!$this->configService->isSupported($currency)) {
            throw CurrencyNotFoundException::forCurrency($currency);
        }
    }

    /**
     * Format amount with proper decimals and rounding.
     *
     * @param float $amount
     * @return float
     */
    protected function formatAmount(float $amount): float
    {
        if ($this->roundResult) {
            return round($amount, $this->amountDecimals);
        }

        // Truncate to specified decimals without rounding
        $multiplier = pow(10, $this->amountDecimals);
        return floor($amount * $multiplier) / $multiplier;
    }
}
