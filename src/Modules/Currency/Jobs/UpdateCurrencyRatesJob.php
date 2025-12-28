<?php

namespace Modules\Currency\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Currency\Services\Api\ExchangeRateSyncService;
use Illuminate\Support\Facades\Log;

class UpdateCurrencyRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes

    /**
     * The base currency to sync. If null, uses default from config.
     *
     * @var string|null
     */
    protected ?string $baseCurrency;

    /**
     * Create a new job instance.
     *
     * @param string|null $baseCurrency
     */
    public function __construct(?string $baseCurrency = null)
    {
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * Execute the job.
     *
     * @param ExchangeRateSyncService $syncService
     * @return void
     */
    public function handle(ExchangeRateSyncService $syncService): void
    {
        try {
            Log::info("Currency rates update job started", [
                'base_currency' => $this->baseCurrency,
                'attempt' => $this->attempts(),
            ]);

            $stats = $syncService->syncAllRates($this->baseCurrency);

            Log::info("Currency rates update job completed successfully", $stats);
        } catch (\Exception $e) {
            Log::error("Currency rates update job failed", [
                'base_currency' => $this->baseCurrency,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Currency rates update job failed permanently", [
            'base_currency' => $this->baseCurrency,
            'error' => $exception->getMessage(),
        ]);
    }
}

