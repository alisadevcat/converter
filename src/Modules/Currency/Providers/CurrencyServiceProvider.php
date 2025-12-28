<?php

namespace Modules\Currency\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Currency\Contracts\CurrencyConverterInterface;
use Modules\Currency\Contracts\Api\CurrencyApiInterface;
use Modules\Currency\Contracts\Converter\CurrencyRateLookupInterface;
use Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use Modules\Currency\Repositories\ExchangeRateRepository;
use Modules\Currency\Repositories\Api\CurrencyApiRepository;
use Modules\Currency\Repositories\Converter\CurrencyRateLookupRepository;
use Modules\Currency\Services\CurrencyConverterService;
use Modules\Currency\Services\CurrencyConfigService;

class CurrencyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            ExchangeRateRepositoryInterface::class,
            ExchangeRateRepository::class
        );

        // Bind converter interface to implementation
        $this->app->bind(
            CurrencyConverterInterface::class,
            CurrencyConverterService::class
        );

        // Bind API interface to implementation
        $this->app->bind(
            CurrencyApiInterface::class,
            CurrencyApiRepository::class
        );

        // Bind converter rate lookup interface to implementation
        $this->app->bind(
            CurrencyRateLookupInterface::class,
            CurrencyRateLookupRepository::class
        );

        // Register CurrencyConfigService as singleton
        $this->app->singleton(CurrencyConfigService::class, function ($app) {
            return new CurrencyConfigService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Merge module configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../Configuration/currency.php',
            'currency'
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');


        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Currency\Console\Commands\UpdateCurrencyRatesCommand::class,
            ]);
        }
    }

    /**
     * Load module routes.
     */
    protected function loadRoutes(): void
    {
        $routesPath = __DIR__ . '/../routes';

        // Load web routes if the file exists
        if (file_exists($routesPath . '/web.php')) {
            Route::middleware('web')
                ->group($routesPath . '/web.php');
        }

        // Load API routes if the file exists
        if (file_exists($routesPath . '/api.php')) {
            Route::middleware('api')
                ->prefix('api')
                ->group($routesPath . '/api.php');
        }
    }

    /**
     * Load module migrations.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}

