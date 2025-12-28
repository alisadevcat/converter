<?php

namespace App\Modules\Currency\Jobs;

use App\Modules\Currency\Contracts\Api\CurrencyApiInterface;
use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\Services\CurrencyConfigService;
use App\Modules\Currency\Models\ExchangeRate;
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
            DB::transaction(function () use ($currencyApi) {
                // Fetch rates from API
                $apiResponse = $currencyApi->fetchLatestRates($this->currencyCode);

                if (!isset($apiResponse['data']) || !is_array($apiResponse['data'])) {
                    throw new \RuntimeException("Invalid API response for {$this->currencyCode}: missing data field");
                }

                $rates = $apiResponse['data'];

                // Map rates to rows array using hardcoded currencies
                $rows = $this->mapRates($rates);

                // Upsert rates
                ExchangeRate::upsert(
                    $rows,
                    ['base_code', 'target_code', 'date'],
                    ['rate', 'updated_at']
                );
            });

            Log::info("Successfully synced rates for {$this->currencyCode}", [
                'date' => $this->date
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $isRateLimitError = str_contains($errorMessage, '429') ||
                               str_contains($errorMessage, 'Too Many Requests') ||
                               str_contains($errorMessage, 'rate limit');

            if ($isRateLimitError) {
                Log::error("Rate limit exceeded for {$this->currencyCode}. Job will retry later.", [
                    'error' => $errorMessage,
                    'attempt' => $this->attempts(),
                    'max_attempts' => $this->tries
                ]);

                // Release job back to queue with delay for rate limit errors
                $this->release(300); // Wait 5 minutes before retry
                return;
            }

            // For other errors, let Laravel handle retry logic
            Log::error("Failed to sync rates for {$this->currencyCode}", [
                'error' => $errorMessage,
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Map API rates to database rows.
     *
     * @param array $rates Rates from API in format ['targetCode' => rate, ...]
     * @return array Array of rows ready for upsert
     */
    private function mapRates(array $rates): array
    {
        $rows = [];
        $now = now();

        foreach ($rates as $targetCode => $rate) {
            // Skip unsupported currencies (validate against hardcoded list)
            if (!CurrencyConfigService::isValidCode($targetCode)) {
                continue;
            }

            // Skip base currency itself (rate would be 1.0)
            if ($targetCode === $this->currencyCode) {
                continue;
            }

            // Skip if rate is not numeric
            if (!is_numeric($rate)) {
                continue;
            }

            $rows[] = [
                'base_code' => $this->currencyCode,
                'target_code' => $targetCode,
                'rate' => (float) $rate,
                'date' => $this->date,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
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

