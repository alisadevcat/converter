<?php

namespace App\Modules\Currency\Repositories\Api;

use App\Modules\Currency\Contracts\Api\CurrencyApiInterface;
use App\Modules\Currency\Exceptions\CurrencyApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class CurrencyApiRepository implements CurrencyApiInterface
{
    private const HTTP_STATUS_RATE_LIMIT = 429;
    private const HTTP_STATUS_UNAUTHORIZED = 401;
    private const HTTP_STATUS_SERVER_ERROR_MIN = 500;

    private Client $client;
    private string $apiKey;
    private string $baseUrl;
    private string $endpoint;
    private int $timeout;
    private string $defaultBaseCurrency;

    public function __construct()
    {
        $this->apiKey = config('currency.api.api_key');
        $this->baseUrl = config('currency.api.base_url');
        $this->endpoint = config('currency.api.endpoint');
        $this->timeout = config('currency.api.timeout');
        $this->defaultBaseCurrency = config('currency.api.default_base_currency');
        $this->client = $this->createHttpClient();
    }

    /**
     * Fetch latest exchange rates from the API.
     *
     * @param string|null $baseCurrency Base currency code (defaults to configured default)
     * @return array Raw API response data containing 'data' field with rates
     * @throws CurrencyApiException If API request fails or data is invalid
     */
    public function fetchLatestRates(?string $baseCurrency = null): array
    {
        $baseCurrency = $baseCurrency ?? $this->defaultBaseCurrency;

        try {
            $response = $this->makeApiRequest($baseCurrency);
            $data = $this->parseResponse($response);
            $this->validateResponse($data);

            return $data;
        } catch (CurrencyApiException $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw $this->handleHttpException($e, $baseCurrency);
        } catch (\Exception $e) {
            throw new CurrencyApiException(
                "Error processing exchange rate data for {$baseCurrency}: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create and configure HTTP client instance.
     *
     * @return Client Configured Guzzle HTTP client
     */
    private function createHttpClient(): Client
    {
        return new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'user-agent' => 'Freecurrencyapi/PHP/0.1',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * Make API request.
     *
     * @param string $baseCurrency
     * @return \Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function makeApiRequest(string $baseCurrency): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->request('GET', $this->endpoint, [
            'query' => [
                'apikey' => $this->apiKey,
                'base_currency' => $baseCurrency,
            ]
        ]);
    }

    /**
     * Parse JSON response from API.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     * @throws CurrencyApiException
     */
    private function parseResponse(\Psr\Http\Message\ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CurrencyApiException(
                'Failed to parse API response: ' . json_last_error_msg()
            );
        }

        if (!is_array($data)) {
            throw new CurrencyApiException('API response is not a valid JSON object');
        }

        return $data;
    }

    /**
     * Validate API response structure.
     *
     * @param array $data
     * @return void
     * @throws CurrencyApiException
     */
    private function validateResponse(array $data): void
    {
        if (!isset($data['data'])) {
            throw new CurrencyApiException('Invalid API response: missing "data" field');
        }

        if (!is_array($data['data'])) {
            throw new CurrencyApiException('Invalid API response: "data" field is not an array');
        }

        if (empty($data['data'])) {
            throw new CurrencyApiException('Invalid API response: "data" field is empty');
        }
    }

    /**
     * Handle HTTP exceptions with more specific error messages.
     *
     * @param GuzzleException $e
     * @param string $baseCurrency
     * @return CurrencyApiException
     */
    private function handleHttpException(GuzzleException $e, string $baseCurrency): CurrencyApiException
    {
        $message = "Unable to fetch exchange rates for {$baseCurrency}";

        if ($e instanceof RequestException && $e->hasResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === self::HTTP_STATUS_RATE_LIMIT) {
                $message .= ': Rate limit exceeded. Please try again later.';
            } elseif ($statusCode === self::HTTP_STATUS_UNAUTHORIZED) {
                $message .= ': Invalid or missing API key. Please check your configuration.';
            } elseif ($statusCode >= self::HTTP_STATUS_SERVER_ERROR_MIN) {
                $message .= ': API server error. Please try again later.';
            } else {
                $message .= ": HTTP {$statusCode}";
            }
        } else {
            $message .= ': ' . $e->getMessage();
        }

        return new CurrencyApiException($message, $e->getCode(), $e);
    }
}