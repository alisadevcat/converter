<?php

namespace Modules\Currency\Console\Commands;

use Illuminate\Console\Command;
use Modules\Currency\Jobs\UpdateCurrencyRatesJob;
use Modules\Currency\Services\Api\ExchangeRateSyncService;

class UpdateCurrencyRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update-rates
                            {--base= : Base currency code (default: from config)}
                            {--sync : Run synchronously instead of dispatching to queue}
                            {--force : Force update even if rates were recently updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update exchange rates from the FreeCurrencyAPI';

    /**
     * Execute the console command.
     *
     * @param ExchangeRateSyncService $syncService
     * @return int
     */
    public function handle(ExchangeRateSyncService $syncService): int
    {
        $baseCurrency = $this->option('base');
        $sync = $this->option('sync');
        $force = $this->option('force');

        $this->info("Starting exchange rate update...");
        $this->info("Base currency: " . ($baseCurrency ?? 'default from config'));

        if ($sync) {
            // Run synchronously
            $this->info("Running synchronously...");
            try {
                $stats = $syncService->syncAllRates($baseCurrency);

                $this->info("✓ Exchange rates updated successfully!");
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Base Currency', $stats['base_currency']],
                        ['Total Rates', $stats['total']],
                        ['Successful', $stats['successful']],
                        ['Failed', $stats['failed']],
                        ['Skipped', $stats['skipped']],
                        ['Duration', $stats['duration_seconds'] . ' seconds'],
                    ]
                );

                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error("✗ Failed to update exchange rates: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            // Dispatch to queue
            $this->info("Dispatching to queue...");
            UpdateCurrencyRatesJob::dispatch($baseCurrency);

            $this->info("✓ Job dispatched successfully!");
            $this->info("Check the queue worker logs for progress.");

            return Command::SUCCESS;
        }
    }
}

