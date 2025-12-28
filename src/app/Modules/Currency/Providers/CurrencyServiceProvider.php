<?php
namespace App\Modules\Currency\Providers;

use App\Modules\Currency\Contracts\Api\CurrencyApiInterface;
use App\Modules\Currency\Repositories\Api\CurrencyApiRepository;
use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\Repositories\ExchangeRateRepository;

use Illuminate\Support\ServiceProvider;

class CurrencyServiceProvider extends ServiceProvider
    {
        /**
         * Register services.
         */
        public function register(): void
        {
            $this->app->bind(CurrencyApiInterface::class, CurrencyApiRepository::class);
            $this->app->bind(ExchangeRateRepositoryInterface::class, ExchangeRateRepository::class);
        }

        /**
         * Bootstrap services.
         */
        public function boot(): void
        {
        $this->mergeConfigFrom(
            __DIR__ . '/../Configuration/api.php',
            'currency.api'
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Currency\Console\CurrencyRatesSync::class,
            ]);
        }
  }
}
