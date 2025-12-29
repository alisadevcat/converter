<?php

namespace App\Modules\Currency\Jobs;

use App\Modules\Currency\Contracts\Api\CurrencyApiInterface;
use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCurrencyExchangeRates implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Delay in seconds before retrying after rate limit error (5 minutes).
     */
    private const RATE_LIMIT_RETRY_DELAY_SECONDS = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $currencyCode,
        private string $date
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        CurrencyApiInterface $currencyApi,
        ExchangeRateRepositoryInterface $exchangeRateRepository,
        CurrencyConfigService $currencyConfigService,
    ): void {
        // Check if this currency already has rates for today
        if ($exchangeRateRepository->hasRatesForDate($this->currencyCode, $this->date)) {
            Log::info("Skipping {$this->currencyCode} - already has rates for {$this->date}");
            return;
        }

        Log::info("Processing currency in queue job", [
            'code' => $this->currencyCode,
            'date' => $this->date
        ]);

        try {
            // Transaction: fetch rates and update DB atomically
            DB::transaction(function () use ($currencyApi, $exchangeRateRepository, $currencyConfigService) {
                // Fetch rates from API
                $apiResponse = $currencyApi->fetchLatestRates($this->currencyCode);

                if (!isset($apiResponse['data']) || !is_array($apiResponse['data'])) {
                    throw new \RuntimeException("Invalid API response for {$this->currencyCode}: missing data field");
                }

                $rates = $apiResponse['data'];

                // Filter rates to only include supported currencies and exclude base currency
                $filteredRates = $this->filterRates($rates, $currencyConfigService);

                // Upsert rates using repository method
                $exchangeRateRepository->upsertDailyRates(
                    $this->currencyCode,
                    $filteredRates,
                    $this->date
                );
            });

            Log::info("Successfully synced rates for {$this->currencyCode}", [
                'date' => $this->date
            ]);

        } catch (\Exception $e) {
            if ($this->isRateLimitError($e)) {
                Log::error("Rate limit exceeded for {$this->currencyCode}. Job will retry later.", [
                    'error' => $e->getMessage(),
                    'attempt' => $this->attempts(),
                    'max_attempts' => $this->tries
                ]);

                // Release job back to queue with delay for rate limit errors
                $this->release(self::RATE_LIMIT_RETRY_DELAY_SECONDS);
                return;
            }

            // For other errors, let Laravel handle retry logic
            Log::error("Failed to sync rates for {$this->currencyCode}", [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Check if the exception indicates a rate limit error.
     *
     * @param \Exception $e The exception to check
     * @return bool True if it's a rate limit error, false otherwise
     */
    private function isRateLimitError(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, '429') ||
               str_contains($message, 'Too Many Requests') ||
               str_contains($message, 'rate limit');
    }

    /**
     * Filter API rates to only include supported currencies and exclude base currency.
     *
     * @param array $rates Rates from API in format ['targetCode' => rate, ...]
     * @param CurrencyConfigService $currencyConfigService
     * @return array Filtered rates array
     */
    private function filterRates(array $rates, CurrencyConfigService $currencyConfigService): array
    {
        $filtered = [];
        $supportedCodes = $currencyConfigService->getSupportedCodes();

        foreach ($rates as $targetCode => $rate) {
            // Skip unsupported currencies (validate against hardcoded list)
            if (!in_array($targetCode, $supportedCodes)) {
                continue;
            }

            // Skip base currency itself (rate would be 1.0)
            if ($targetCode === $this->currencyCode) {
                continue;
            }

            // Skip if rate is not numeric (repository will also check this, but filter early)
            if (!is_numeric($rate)) {
                continue;
            }

            $filtered[$targetCode] = $rate;
        }

        return $filtered;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed permanently for {$this->currencyCode}", [
            'error' => $exception->getMessage(),
            'currency_code' => $this->currencyCode,
            'date' => $this->date
        ]);
    }
}

