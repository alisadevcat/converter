<?php

namespace App\Modules\Currency\Controllers;

use App\Modules\Currency\Requests\ConvertCurrencyRequest;
use App\Modules\Currency\Services\ConverterService;
use App\Modules\Currency\Services\CurrencyConfigService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CurrencyConverterController extends Controller
{
    public function __construct(
        private ConverterService $converterService
    ) {}

    /**
     * Display the welcome page with currency converter.
     */
    public function index(): Response
    {
        $currencies = CurrencyConfigService::getSupportedCodes();
        return Inertia::render('Converter/Index', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Convert currency.
     */
    public function convert(ConvertCurrencyRequest $request): Response
    {
        $validated = $request->validated();
        $currencies = CurrencyConfigService::getSupportedCodes();
        try {
            $result = $this->converterService->convert(
                (float) $validated['amount'],
                $validated['from_currency'],
                $validated['to_currency']
            );

            return Inertia::render('Converter/Index', [
                'converterResult' => $result->toArray(),
                'currencies' => $currencies,
            ]);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return Inertia::render('Converter/Index', [
                'converterError' => $e->getMessage(),
                'currencies' => $currencies,
            ]);
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Unexpected error in currency conversion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('Converter/Index', [
                'converterError' => 'Unable to process conversion at this time. Please try again later or contact support if the issue persists.',
                'currencies' => $currencies,
            ]);
        }
    }
}
