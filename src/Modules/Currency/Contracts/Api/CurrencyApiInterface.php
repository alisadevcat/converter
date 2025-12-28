<?php

namespace Modules\Currency\Contracts\Api;

interface CurrencyApiInterface
{
    /**
     * Fetch latest exchange rates from the API.
     *
     * @param string|null $baseCurrency The base currency code (e.g., 'USD'). If null, uses default from config.
     * @return array Array of rates with currency codes as keys
     * @throws \Modules\Currency\Exceptions\CurrencyApiException
     */
    public function fetchLatestRates(?string $baseCurrency = null): array;
}

