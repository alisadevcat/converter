<?php

namespace Modules\Currency\Repositories\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Modules\Currency\Contracts\Api\CurrencyApiInterface;
use Modules\Currency\Exceptions\CurrencyApiException;
use Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Support\Facades\Log;

class CurrencyApiRepository implements CurrencyApiInterface
{
    protected Client $httpClient;
    protected CurrencyConfigService $configService;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct(CurrencyConfigService $configService)
    {
        $this->configService = $configService;
        $this->baseUrl = config('currency.api.base_url', 'https://api.freecurrencyapi.com/v1');
        $this->apiKey = config('currency.api.api_key', '');
        $this->timeout = config('currency.api.timeout', 30);

        $this->httpClient = new Client([
            'timeout' => $this->timeout,
            'verify' => true,
        ]);
    }

    /**
     * Fetch latest exchange rates from the API.
     *
     * @param string|null $baseCurrency The base currency code (e.g., 'USD'). If null, uses default from config.
     * @return array Array of rates with currency codes as keys
     * @throws CurrencyApiException
     */
    public function fetchLatestRates(?string $baseCurrency = null): array
    {
        // Validate API key
        if (empty($this->apiKey)) {
            throw CurrencyApiException::missingApiKey();
        }

        // Use provided base currency or default from config
        $baseCurrency = $baseCurrency ?? config('currency.default_base_currency', 'USD');
        $baseCurrency = strtoupper($baseCurrency);

        // Validate base currency
        if (!$this->configService->isSupported($baseCurrency)) {
            throw CurrencyApiException::requestFailed("Unsupported base currency: {$baseCurrency}");
        }

        try {
            $response = $this->makeRequest($baseCurrency);
            return $this->parseResponse($response);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            throw CurrencyApiException::requestFailed(
                'Failed to fetch exchange rates from API',
                $e->getCode(),
                $e
            );
        } catch (GuzzleException $e) {
            Log::error('Currency API Guzzle exception', [
                'message' => $e->getMessage(),
                'base_currency' => $baseCurrency,
            ]);
            throw CurrencyApiException::requestFailed(
                'Network error while fetching exchange rates: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (\Exception $e) {
            Log::error('Currency API unexpected exception', [
                'message' => $e->getMessage(),
                'base_currency' => $baseCurrency,
            ]);
            throw CurrencyApiException::requestFailed(
                'Unexpected error: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Make HTTP request to the API.
     *
     * @param string $baseCurrency
     * @return array
     * @throws GuzzleException
     */
    protected function makeRequest(string $baseCurrency): array
    {
        $url = $this->baseUrl . '/latest';

        $response = $this->httpClient->get($url, [
            'query' => [
                'apikey' => $this->apiKey,
                'base_currency' => $baseCurrency,
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw CurrencyApiException::requestFailed(
                "API returned status code: {$statusCode}"
            );
        }

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw CurrencyApiException::invalidResponse(
                'Failed to parse JSON response: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * Parse API response and extract rates.
     *
     * @param array $response
     * @return array
     * @throws CurrencyApiException
     */
    protected function parseResponse(array $response): array
    {
        // Check if response has the expected structure
        if (!isset($response['data']) || !is_array($response['data'])) {
            throw CurrencyApiException::invalidResponse(
                'API response does not contain expected "data" field'
            );
        }

        $rates = $response['data'];

        // Validate that rates is not empty
        if (empty($rates)) {
            throw CurrencyApiException::invalidResponse(
                'API response contains no exchange rates'
            );
        }

        // Ensure all rates are numeric
        foreach ($rates as $currency => $rate) {
            if (!is_numeric($rate)) {
                Log::warning('Invalid rate value received from API', [
                    'currency' => $currency,
                    'rate' => $rate,
                ]);
                unset($rates[$currency]);
            }
        }

        if (empty($rates)) {
            throw CurrencyApiException::invalidResponse(
                'No valid exchange rates found in API response'
            );
        }

        return $rates;
    }

    /**
     * Handle request exceptions with detailed error information.
     *
     * @param RequestException $e
     * @return void
     * @throws CurrencyApiException
     */
    protected function handleRequestException(RequestException $e): void
    {
        $statusCode = null;
        $responseBody = null;

        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            Log::error('Currency API request failed', [
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'message' => $e->getMessage(),
            ]);

            // Try to parse error message from response
            $errorData = json_decode($responseBody, true);
            if (isset($errorData['message'])) {
                throw CurrencyApiException::requestFailed(
                    "API Error: {$errorData['message']}",
                    $statusCode,
                    $e
                );
            }

            // Handle specific status codes
            match ($statusCode) {
                401 => throw CurrencyApiException::requestFailed(
                    'API authentication failed. Please check your API key.',
                    $statusCode,
                    $e
                ),
                403 => throw CurrencyApiException::requestFailed(
                    'API access forbidden. Please check your API key permissions.',
                    $statusCode,
                    $e
                ),
                429 => throw CurrencyApiException::requestFailed(
                    'API rate limit exceeded. Please try again later.',
                    $statusCode,
                    $e
                ),
                500, 502, 503, 504 => throw CurrencyApiException::requestFailed(
                    'API server error. Please try again later.',
                    $statusCode,
                    $e
                ),
                default => null,
            };
        } else {
            Log::error('Currency API request failed without response', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}

