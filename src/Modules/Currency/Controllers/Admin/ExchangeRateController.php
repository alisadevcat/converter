<?php

namespace App\Modules\Currency\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Currency\Resources\ExchangeRateResource;
use App\Modules\Currency\Services\CurrencyConfigService;
use App\Modules\Currency\Services\ExchangeRateService;
use App\Modules\Currency\Services\Api\ExchangeRateSyncService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function __construct(
        private ExchangeRateService $exchangeRateService,
        private ExchangeRateSyncService $exchangeRateSyncService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $currencies = CurrencyConfigService::getSupportedCodes();
        $exchangeRates = CurrencyConfigService::isValidCode('USD')
            ? $this->exchangeRateService->getLatestRatesByBaseCurrencyCode('USD')
            : collect();

        return Inertia::render('Admin/ExchangeRates/Index', [
            'exchangeRates' => ExchangeRateResource::collection($exchangeRates),
            'currencies' => $currencies
        ]);
    }

    /**
     * Display exchange rates for a specific base currency.
     *
     * @param string $baseCurrency The currency code (e.g., 'USD', 'EUR')
     */
    public function show(string $baseCurrency): Response
    {
        $currencies = CurrencyConfigService::getSupportedCodes();
        $exchangeRates = CurrencyConfigService::isValidCode($baseCurrency)
            ? $this->exchangeRateService->getLatestRatesByBaseCurrencyCode($baseCurrency)
            : collect();

        return Inertia::render('Admin/ExchangeRates/Index', [
            'exchangeRates' => ExchangeRateResource::collection($exchangeRates),
            'currencies' => $currencies
        ]);
    }

    /**
     * Sync daily exchange rates by dispatching queue jobs. Temporary method for testing manual update.
     */
    public function syncDailyRates(): RedirectResponse
    {
        try {
            $this->exchangeRateSyncService->syncDailyRates();

            return redirect()->back()->with('success',
                'Exchange rate sync jobs have been dispatched to the queue. ' .
                'Rates will be synced asynchronously. Make sure your queue worker is running.'
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error',
                'Failed to dispatch sync jobs: ' . $e->getMessage()
            );
        }
    }
}
