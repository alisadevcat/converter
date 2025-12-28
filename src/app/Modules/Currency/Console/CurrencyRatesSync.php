<?php

namespace App\Modules\Currency\Console;

use Illuminate\Console\Command;
use App\Modules\Currency\Services\Api\ExchangeRateSyncService;

class CurrencyRatesSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:currency-rates-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync daily exchange rates for all currencies from the API';

    /**
     * Execute the console command.
     *
     * @param ExchangeRateSyncService $service
     * @return int Command exit code
     */
    public function handle(ExchangeRateSyncService $service): int
    {
        $service->syncDailyRates();
        return self::SUCCESS;
    }
}
