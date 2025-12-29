<?php

namespace App\Modules\Currency\Services\Api;

use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\Jobs\SyncCurrencyExchangeRates;
use App\Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExchangeRateSyncService
{
    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository,
        private CurrencyConfigService $currencyConfigService
    ) {}

    /**
     * Get delay between job dispatches in seconds.
     * Can be configured via config or defaults to request_delay value.
     */
    private function getJobDelay(): int
    {
        return config('currency.api.job_delay', config('currency.api.request_delay', 2));
    }

    /**
     * Dispatch jobs to sync exchange rates for all currencies.
     * Each currency is processed as a separate queue job with delays.
     */
    public function syncDailyRates(): void
    {
        $today = Carbon::today()->toDateString();
        $currencyCodes = $this->currencyConfigService->getSupportedCodes();

        if (empty($currencyCodes)) {
            Log::error('No currencies found in configuration.');
            throw new \RuntimeException('No currencies found in configuration.');
        }

        $totalCurrencies = count($currencyCodes);
        $jobDelay = $this->getJobDelay();

        Log::info("Found {$totalCurrencies} currencies to sync", [
            'currencies' => $currencyCodes,
            'job_delay_seconds' => $jobDelay
        ]);

        // Get currencies that already have rates for today (to skip them)
        $existingRates = $this->exchangeRateRepository->getBaseCurrencyCodesWithRatesForDate($today);

        $skippedCount = 0;
        $dispatchedCount = 0;
        $delaySeconds = 0;

        // Dispatch a job for each currency with incremental delays
        foreach ($currencyCodes as $currencyCode) {
            // Skip if this currency already has rates for today
            if (in_array($currencyCode, $existingRates)) {
                $skippedCount++;
                Log::debug("Skipping {$currencyCode} - already has rates for today", [
                    'code' => $currencyCode,
                    'date' => $today
                ]);
                continue;
            }

            // Dispatch job with delay to space out API requests
            SyncCurrencyExchangeRates::dispatch($currencyCode, $today)
                ->delay(now()->addSeconds($delaySeconds));

            $dispatchedCount++;
            $delaySeconds += $jobDelay;

            Log::info("Dispatched job for currency", [
                'code' => $currencyCode,
                'delay_seconds' => $delaySeconds - $jobDelay
            ]);
        }

        Log::info('Daily exchange rates sync jobs dispatched', [
            'total_currencies' => $totalCurrencies,
            'jobs_dispatched' => $dispatchedCount,
            'skipped' => $skippedCount,
            'estimated_completion_seconds' => $delaySeconds,
            'date' => $today
        ]);
    }
}