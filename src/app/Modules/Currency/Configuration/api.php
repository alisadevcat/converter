<?php

/**
 * Currency API Configuration
 *
 * Configuration for the FreeCurrencyAPI integration.
 * API key should be set in .env file as CURRENCY_API_KEY
 */
return [
    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the FreeCurrencyAPI service.
    |
    */
    'base_url' => env('CURRENCY_API_BASE_URL', 'https://api.freecurrencyapi.com'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoint
    |--------------------------------------------------------------------------
    |
    | The API endpoint path for fetching latest exchange rates.
    |
    */
    'endpoint' => env('CURRENCY_API_ENDPOINT', 'v1/latest'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your FreeCurrencyAPI API key. Get one at https://freecurrencyapi.com/
    |
    */
    'api_key' => env('CURRENCY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for API requests.
    |
    */
    'timeout' => env('CURRENCY_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Base Currency
    |--------------------------------------------------------------------------
    |
    | The default base currency code used when none is specified.
    |
    */
    'default_base_currency' => env('CURRENCY_DEFAULT_BASE', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Request Delay
    |--------------------------------------------------------------------------
    |
    | Delay in seconds between API requests to prevent rate limiting.
    |
    */
    'request_delay' => env('CURRENCY_REQUEST_DELAY', 3),

    /*
    |--------------------------------------------------------------------------
    | Job Delay
    |--------------------------------------------------------------------------
    |
    | Delay in seconds between queue job dispatches when syncing multiple currencies.
    |
    */
    'job_delay' => env('CURRENCY_JOB_DELAY', 5),
];

