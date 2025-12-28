<?php

namespace Modules\Currency\Exceptions;

use Exception;

/**
 * Exception thrown when Currency API operations fail.
 */
class CurrencyApiException extends Exception
{
    /**
     * Create a new exception instance for API request failure.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @return static
     */
    public static function requestFailed(string $message, int $code = 0, ?\Throwable $previous = null): static
    {
        return new static("Currency API request failed: {$message}", $code, $previous);
    }

    /**
     * Create a new exception instance for invalid API response.
     *
     * @param string $message
     * @return static
     */
    public static function invalidResponse(string $message = 'Invalid response from currency API'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception instance for missing API key.
     *
     * @return static
     */
    public static function missingApiKey(): static
    {
        return new static('FreeCurrencyAPI key is not configured. Please set FREECURRENCYAPI_KEY in your .env file.');
    }
}