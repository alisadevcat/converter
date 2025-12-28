<?php

namespace Modules\Currency\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Currency\Services\ExchangeRateService;
use Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController extends Controller
{
    public function __construct(
        protected ExchangeRateService $exchangeRateService,
        protected CurrencyConfigService $configService
    ) {
    }

    /**
     * Display exchange rates index page (redirects to default currency).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $defaultCurrency = $this->configService->getDefaultBaseCurrency();
        return redirect()->route('admin.exchange-rates.show', $defaultCurrency);
    }

    /**
     * Display exchange rates for a specific base currency.
     *
     * @param string $baseCurrency
     * @return Response
     */
    public function show(string $baseCurrency): Response
    {
        $baseCurrency = strtoupper($baseCurrency);

        // Validate base currency
        if (!$this->configService->isSupported($baseCurrency)) {
            $defaultCurrency = $this->configService->getDefaultBaseCurrency();
            return redirect()->route('admin.exchange-rates.show', $defaultCurrency);
        }

        // Get exchange rates for the selected base currency
        $rates = $this->exchangeRateService->getRatesByBaseCurrency($baseCurrency);

        // Format rates for frontend
        $formattedRates = $rates->map(function ($rate) {
            return [
                'id' => $rate->id,
                'base' => $rate->base_code,
                'target' => $rate->target_code,
                'rate' => (float) $rate->rate,
                'date' => $rate->date->format('Y-m-d'),
                'updated_at' => $rate->updated_at->format('Y-m-d H:i:s'),
            ];
        })->values();

        // Get all currencies for the select dropdown
        $currencies = collect($this->configService->getCurrencies())->map(function ($currency, $code) {
            return [
                'code' => $code,
                'name' => $currency['name'],
                'symbol' => $currency['symbol'],
            ];
        })->values();

        return Inertia::render('Admin/ExchangeRates/Index', [
            'rates' => $formattedRates,
            'currencies' => $currencies,
            'selectedBaseCurrency' => $baseCurrency,
            'defaultBaseCurrency' => $this->configService->getDefaultBaseCurrency(),
        ]);
    }
}

