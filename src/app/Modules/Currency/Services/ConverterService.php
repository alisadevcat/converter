<?php

namespace App\Modules\Currency\Services;

use App\Modules\Currency\Contracts\CurrencyConverterInterface;
use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\DTOs\CurrencyConversionResult;
use App\Modules\Currency\Models\ExchangeRate;
use App\Modules\Currency\Services\CurrencyConfigService;
use Carbon\Carbon;

class ConverterService implements CurrencyConverterInterface
{
    private const USD_CODE = 'USD';

    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository,
        private CurrencyConfigService $currencyConfigService
    ) {}

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
    public function convert(float $amount, string $fromCurrency, string $toCurrency): CurrencyConversionResult
    {
        // Validate currency codes (this should already be validated by the request, but double-check for safety)
        if (!$this->currencyConfigService->isValidCode($fromCurrency)) {
            throw new \InvalidArgumentException("The source currency code '{$fromCurrency}' is not supported. Please select a valid currency.");
        }

        if (!$this->currencyConfigService->isValidCode($toCurrency)) {
            throw new \InvalidArgumentException("The target currency code '{$toCurrency}' is not supported. Please select a valid currency.");
        }

        // If same currency, return 1:1
        if ($fromCurrency === $toCurrency) {
            return CurrencyConversionResult::sameCurrency($amount, $fromCurrency);
        }

        // Get today's date
        $today = Carbon::today()->toDateString();

        // Try to get direct exchange rate for today
        $exchangeRate = $this->exchangeRateRepository->findRate(
            $fromCurrency,
            $toCurrency,
            $today
        );

        // If direct rate not found, try to get latest available rate
        if (!$exchangeRate) {
            $exchangeRate = $this->exchangeRateRepository->findLatestRate(
                $fromCurrency,
                $toCurrency
            );
        }

        // If still not found, try converting via USD
        if (!$exchangeRate) {
            $rate = $this->convertViaUsd($fromCurrency, $toCurrency, $today);

            if ($rate === null) {
                throw new \RuntimeException("Exchange rate not available for converting from {$fromCurrency} to {$toCurrency}. Please try again later or contact support if the issue persists.");
            }

            $convertedAmount = $amount * $rate;

            return CurrencyConversionResult::fromValues(
                $amount,
                $fromCurrency,
                $toCurrency,
                $convertedAmount,
                $rate
            );
        }

        // Calculate converted amount
        $convertedAmount = $amount * $exchangeRate->rate;

        return CurrencyConversionResult::fromValues(
            $amount,
            $fromCurrency,
            $toCurrency,
            $convertedAmount,
            (float) $exchangeRate->rate
        );
    }

    /**
     * Convert currency via USD if direct rate is not available.
     *
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param string $date
     * @return float|null
     */
    private function convertViaUsd(string $fromCurrencyCode, string $toCurrencyCode, string $date): ?float
    {
        if (!$this->currencyConfigService->isValidCode(self::USD_CODE)) {
            return null;
        }

        // If converting from USD
        if ($fromCurrencyCode === self::USD_CODE) {
            return $this->convertFromUsd($toCurrencyCode, $date);
        }

        // If converting to USD
        if ($toCurrencyCode === self::USD_CODE) {
            return $this->convertToUsd($fromCurrencyCode, $date);
        }

        // Convert from -> USD -> to
        return $this->convertViaUsdPair($fromCurrencyCode, $toCurrencyCode, $date);
    }

    /**
     * Convert from USD to another currency.
     *
     * @param string $toCurrencyCode
     * @param string $date
     * @return float|null
     */
    private function convertFromUsd(string $toCurrencyCode, string $date): ?float
    {
        $rate = $this->findRateWithFallback(self::USD_CODE, $toCurrencyCode, $date);

        return $rate ? (float) $rate->rate : null;
    }

    /**
     * Convert to USD from another currency.
     *
     * @param string $fromCurrencyCode
     * @param string $date
     * @return float|null
     */
    private function convertToUsd(string $fromCurrencyCode, string $date): ?float
    {
        $rate = $this->findRateWithFallback($fromCurrencyCode, self::USD_CODE, $date);

        return $rate ? (1 / (float) $rate->rate) : null;
    }

    /**
     * Convert between two non-USD currencies via USD.
     *
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param string $date
     * @return float|null
     */
    private function convertViaUsdPair(string $fromCurrencyCode, string $toCurrencyCode, string $date): ?float
    {
        $fromToUsd = $this->findRateWithFallback($fromCurrencyCode, self::USD_CODE, $date);
        $usdToTo = $this->findRateWithFallback(self::USD_CODE, $toCurrencyCode, $date);

        if (!$fromToUsd || !$usdToTo) {
            return null;
        }

        // Calculate: (1 / fromToUsd) * usdToTo
        return (1 / (float) $fromToUsd->rate) * (float) $usdToTo->rate;
    }

    /**
     * Find exchange rate with fallback to latest available rate.
     *
     * @param string $baseCurrencyCode
     * @param string $targetCurrencyCode
     * @param string $date
     * @return ExchangeRate|null
     */
    private function findRateWithFallback(string $baseCurrencyCode, string $targetCurrencyCode, string $date): ?ExchangeRate
    {
        $rate = $this->exchangeRateRepository->findRate($baseCurrencyCode, $targetCurrencyCode, $date);

        if (!$rate) {
            $rate = $this->exchangeRateRepository->findLatestRate($baseCurrencyCode, $targetCurrencyCode);
        }

        return $rate;
    }
}

