<?php

namespace App\Modules\Currency\Services;

class CurrencyConfigService
{
    private array $currencies;

    public function __construct()
    {
        $this->currencies = require __DIR__ . '/../Configuration/currencies.php';
    }

    public function getSupportedCodes(): array
    {
        return array_keys($this->currencies);
    }

    public function getActiveCodes(): array
    {
        // All currencies in the map are considered active
        return $this->getSupportedCodes();
    }

    public function getCurrencyInfo(string $code): ?array
    {
        return $this->currencies[$code] ?? null;
    }

    public function isValidCode(string $code): bool
    {
        return isset($this->currencies[$code]);
    }

    public function getName(string $code): ?string
    {
        return $this->currencies[$code]['name'] ?? null;
    }

    public function getSymbol(string $code): ?string
    {
        return $this->currencies[$code]['symbol'] ?? null;
    }

    public function getCurrencies(): array
    {
        return $this->currencies;
    }
}