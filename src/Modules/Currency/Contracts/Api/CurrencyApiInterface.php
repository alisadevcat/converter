<?php

namespace App\Modules\Currency\Contracts\Api;

use App\Modules\Currency\Exceptions\CurrencyApiException;

interface CurrencyApiInterface
{
    /**
     * Fetch latest exchange rates from the API.
     *
     * @param string|null $baseCurrency Base currency code (defaults to 'USD')
     * @return array Raw API response data containing 'data' field with rates
     * @throws CurrencyApiException If API request fails or data is invalid
     */
    public function fetchLatestRates(?string $baseCurrency = null): array;
}

