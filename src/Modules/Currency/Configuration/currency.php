<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | This array contains all supported currencies with their names and symbols.
    | Currencies are hardcoded as per requirements.
    |
    */

    'currencies' => [
        'EUR' => ['name' => 'Euro', 'symbol' => '€'],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
        'BGN' => ['name' => 'Bulgarian Lev', 'symbol' => 'лв'],
        'CZK' => ['name' => 'Czech Republic Koruna', 'symbol' => 'Kč'],
        'DKK' => ['name' => 'Danish Krone', 'symbol' => 'kr'],
        'GBP' => ['name' => 'British Pound Sterling', 'symbol' => '£'],
        'HUF' => ['name' => 'Hungarian Forint', 'symbol' => 'Ft'],
        'PLN' => ['name' => 'Polish Zloty', 'symbol' => 'zł'],
        'RON' => ['name' => 'Romanian Leu', 'symbol' => 'lei'],
        'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'kr'],
        'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'Fr'],
        'ISK' => ['name' => 'Icelandic Króna', 'symbol' => 'kr'],
        'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'kr'],
        'HRK' => ['name' => 'Croatian Kuna', 'symbol' => 'kn'],
        'RUB' => ['name' => 'Russian Ruble', 'symbol' => '₽'],
        'TRY' => ['name' => 'Turkish Lira', 'symbol' => '₺'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
        'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
        'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
        'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$'],
        'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
        'ILS' => ['name' => 'Israeli New Sheqel', 'symbol' => '₪'],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
        'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩'],
        'MXN' => ['name' => 'Mexican Peso', 'symbol' => '$'],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
        'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
        'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
        'THB' => ['name' => 'Thai Baht', 'symbol' => '฿'],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Base Currency
    |--------------------------------------------------------------------------
    |
    | The default base currency used for fetching exchange rates from the API.
    | Most APIs use USD as the base currency.
    |
    */

    'default_base_currency' => env('CURRENCY_BASE', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | FreeCurrencyAPI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the FreeCurrencyAPI service.
    |
    */

    'api' => [
        'base_url' => env('FREECURRENCYAPI_BASE_URL', 'https://api.freecurrencyapi.com/v1'),
        'api_key' => env('FREECURRENCYAPI_KEY'),
        'timeout' => env('FREECURRENCYAPI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for daily exchange rate synchronization.
    |
    */

    'sync' => [
        'delay_between_calls' => env('CURRENCY_SYNC_DELAY', 1), // seconds between API calls
        'max_retries' => env('CURRENCY_SYNC_MAX_RETRIES', 3), // maximum retry attempts
        'retry_delay' => env('CURRENCY_SYNC_RETRY_DELAY', 5), // base delay for retries (seconds)
        'schedule_time' => env('CURRENCY_SYNC_SCHEDULE_TIME', '00:00'), // daily sync time (H:i format)
    ],
];
