<?php

namespace App\Modules\Currency\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Currency\Contracts\ExchangeRateRepositoryInterface;
use App\Modules\Currency\Resources\ExchangeRateResource;
use App\Modules\Currency\Services\CurrencyConfigService;
use App\Modules\Currency\Services\Api\ExchangeRateSyncService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function __construct(
        private ExchangeRateRepositoryInterface $exchangeRateRepository,
        private ExchangeRateSyncService $exchangeRateSyncService,
        private CurrencyConfigService $currencyConfigService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $currencies = $this->currencyConfigService->getSupportedCodes();
        $exchangeRates = $this->currencyConfigService->isValidCode('USD')
            ? $this->exchangeRateRepository->getLatestRatesByBaseCurrencyCode('USD')
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
        if (!$this->currencyConfigService->isValidCode($baseCurrency)) {
            abort(404, 'Currency not found');
        }

        $currencies = $this->currencyConfigService->getSupportedCodes();
        $exchangeRates = $this->exchangeRateRepository->getLatestRatesByBaseCurrencyCode($baseCurrency);

        return Inertia::render('Admin/ExchangeRates/Index', [
            'exchangeRates' => ExchangeRateResource::collection($exchangeRates),
            'currencies' => $currencies
        ]);
    }

    /**
     * Sync daily exchange rates by dispatching queue jobs.
     */
    public function syncDailyRates(): RedirectResponse
    {
        // Validate queue configuration
        $queueDriver = config('queue.default');
        if (!$queueDriver || $queueDriver === 'sync') {
            return redirect()->back()->with('error',
                'Queue is not properly configured. Please configure a queue driver (e.g., database, redis) in your .env file.'
            );
        }

        // Check if API key is configured
        if (empty(config('currency.api.api_key'))) {
            return redirect()->back()->with('error',
                'Currency API key is not configured. Please set CURRENCY_API_KEY in your .env file.'
            );
        }

        try {
            $this->exchangeRateSyncService->syncDailyRates();

            return redirect()->back()->with('success',
                'Exchange rate sync jobs have been dispatched to the queue. ' .
                'Rates will be synced asynchronously. Make sure your queue worker is running.'
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error',
                'Failed to dispatch sync jobs. Error: ' . $e->getMessage() . ' Please check your configuration and try again.'
            );
        }
    }
}
