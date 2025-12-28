<?php

namespace Modules\Currency\Services\Api;

use Modules\Currency\Contracts\Api\CurrencyApiInterface;
use Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Modules\Currency\Exceptions\CurrencyApiException;
use Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExchangeRateSyncService
{
    protected int $delayBetweenCalls;
    protected int $maxRetries;
    protected int $retryDelay;

    public function __construct(
        protected CurrencyApiInterface $apiRepository,
        protected ExchangeRateRepositoryInterface $exchangeRateRepository,
        protected CurrencyConfigService $configService
    ) {
        // Configuration for rate limiting and retries
        $this->delayBetweenCalls = config('currency.sync.delay_between_calls', 1); // seconds
        $this->maxRetries = config('currency.sync.max_retries', 3);
        $this->retryDelay = config('currency.sync.retry_delay', 5); // seconds
    }

    /**
     * Sync all exchange rates from the API.
     *
     * @param string|null $baseCurrency Base currency to sync. If null, uses default from config.
     * @return array Statistics about the sync operation
     * @throws CurrencyApiException
     */
    public function syncAllRates(?string $baseCurrency = null): array
    {
        $baseCurrency = $baseCurrency ?? $this->configService->getDefaultBaseCurrency();
        $baseCurrency = strtoupper($baseCurrency);

        Log::info("Starting exchange rate sync", ['base_currency' => $baseCurrency]);

        $stats = [
            'base_currency' => $baseCurrency,
            'started_at' => now(),
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'total' => 0,
        ];

        try {
            // Fetch rates from API with retry logic
            $rates = $this->fetchRatesWithRetry($baseCurrency);

            // Process and save rates
            $result = $this->saveRates($baseCurrency, $rates, now()->toDateString());

            $stats['successful'] = $result['successful'];
            $stats['failed'] = $result['failed'];
            $stats['skipped'] = $result['skipped'];
            $stats['total'] = count($rates);
            $stats['completed_at'] = now();
            $stats['duration_seconds'] = $stats['started_at']->diffInSeconds($stats['completed_at']);

            Log::info("Exchange rate sync completed", $stats);

            return $stats;
        } catch (CurrencyApiException $e) {
            Log::error("Exchange rate sync failed", [
                'base_currency' => $baseCurrency,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Fetch rates from API with retry logic and rate limiting.
     *
     * @param string $baseCurrency
     * @return array
     * @throws CurrencyApiException
     */
    protected function fetchRatesWithRetry(string $baseCurrency): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                // Add delay between API calls (except for first call)
                if ($attempt > 0) {
                    $delay = $this->retryDelay * pow(2, $attempt - 1); // Exponential backoff
                    Log::info("Waiting before retry", [
                        'attempt' => $attempt + 1,
                        'delay_seconds' => $delay,
                    ]);
                    sleep($delay);
                }

                $rates = $this->apiRepository->fetchLatestRates($baseCurrency);

                // If successful, return rates
                if (!empty($rates)) {
                    return $rates;
                }

                throw CurrencyApiException::invalidResponse('Empty rates returned from API');
            } catch (CurrencyApiException $e) {
                $lastException = $e;
                $attempt++;

                // Check if it's a rate limit error (429)
                if ($e->getCode() === 429) {
                    Log::warning("Rate limit hit, will retry", [
                        'attempt' => $attempt,
                        'max_retries' => $this->maxRetries,
                    ]);
                    continue;
                }

                // For other errors, log and retry if attempts remain
                if ($attempt < $this->maxRetries) {
                    Log::warning("API call failed, retrying", [
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }

                // Max retries reached
                throw $e;
            }
        }

        // If we get here, all retries failed
        throw $lastException ?? CurrencyApiException::requestFailed('Failed to fetch rates after all retries');
    }

    /**
     * Save exchange rates to database using batch upsert.
     *
     * @param string $baseCurrency
     * @param array $rates
     * @param string $date
     * @return array Statistics about saved rates
     */
    protected function saveRates(string $baseCurrency, array $rates, string $date): array
    {
        $stats = [
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        // Prepare data for batch insert/update
        $dataToInsert = [];
        $supportedCurrencies = $this->configService->getCurrencyCodes();

        foreach ($rates as $targetCurrency => $rate) {
            $targetCurrency = strtoupper($targetCurrency);

            // Skip if target currency is not supported
            if (!in_array($targetCurrency, $supportedCurrencies)) {
                $stats['skipped']++;
                continue;
            }

            // Skip if same as base currency (rate would be 1.0)
            if ($targetCurrency === $baseCurrency) {
                $stats['skipped']++;
                continue;
            }

            // Validate rate is numeric
            if (!is_numeric($rate) || $rate <= 0) {
                Log::warning("Invalid rate value, skipping", [
                    'base_currency' => $baseCurrency,
                    'target_currency' => $targetCurrency,
                    'rate' => $rate,
                ]);
                $stats['failed']++;
                continue;
            }

            $dataToInsert[] = [
                'base_code' => $baseCurrency,
                'target_code' => $targetCurrency,
                'rate' => (float) $rate,
                'date' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Use transaction for atomicity
        try {
            DB::beginTransaction();

            // Batch upsert using updateOrCreate for each rate
            // This handles duplicate key errors gracefully
            foreach ($dataToInsert as $data) {
                try {
                    $this->exchangeRateRepository->updateOrCreate(
                        [
                            'base_code' => $data['base_code'],
                            'target_code' => $data['target_code'],
                            'date' => $data['date'],
                        ],
                        [
                            'rate' => $data['rate'],
                            'updated_at' => $data['updated_at'],
                        ]
                    );
                    $stats['successful']++;
                } catch (\Exception $e) {
                    Log::error("Failed to save exchange rate", [
                        'base_currency' => $data['base_code'],
                        'target_currency' => $data['target_code'],
                        'error' => $e->getMessage(),
                    ]);
                    $stats['failed']++;
                }
            }

            DB::commit();

            Log::info("Exchange rates saved to database", [
                'base_currency' => $baseCurrency,
                'date' => $date,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Transaction failed while saving exchange rates", [
                'base_currency' => $baseCurrency,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $stats;
    }

    /**
     * Sync rates for a specific date (for historical data).
     *
     * @param string $date Date in Y-m-d format
     * @param string|null $baseCurrency
     * @return array
     */
    public function syncRatesForDate(string $date, ?string $baseCurrency = null): array
    {
        // Note: FreeCurrencyAPI free tier might not support historical data
        // This method is here for future extensibility
        $baseCurrency = $baseCurrency ?? $this->configService->getDefaultBaseCurrency();
        $baseCurrency = strtoupper($baseCurrency);

        Log::info("Syncing exchange rates for specific date", [
            'base_currency' => $baseCurrency,
            'date' => $date,
        ]);

        // For now, just sync latest rates and use the provided date
        $rates = $this->fetchRatesWithRetry($baseCurrency);
        return $this->saveRates($baseCurrency, $rates, $date);
    }
}

