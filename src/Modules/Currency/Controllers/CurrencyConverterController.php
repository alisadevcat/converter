<?php

namespace Modules\Currency\Controllers;

use App\Http\Controllers\Controller;
use Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurrencyConverterController extends Controller
{
    public function __construct(
        protected CurrencyConfigService $configService
    ) {
    }

    /**
     * Display the currency converter page.
     *
     * @return Response
     */
    public function index(): Response
    {
        // Get all currencies for the dropdowns
        $currencies = collect($this->configService->getCurrencies())->map(function ($currency, $code) {
            return [
                'code' => $code,
                'name' => $currency['name'],
                'symbol' => $currency['symbol'],
            ];
        })->values();

        return Inertia::render('Converter/Index', [
            'currencies' => $currencies,
            'defaultBaseCurrency' => $this->configService->getDefaultBaseCurrency(),
        ]);
    }
}

