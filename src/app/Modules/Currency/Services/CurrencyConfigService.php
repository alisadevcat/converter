<?php

namespace App\Modules\Currency\Services;

class CurrencyConfigService
{
    private static ?array $currencies = null;

    private static function getCurrencies(): array
    {
        if (self::$currencies === null) {
            self::$currencies = require __DIR__ . '/../Configuration/currencies.php';
        }
        return self::$currencies;
    }

    public static function getSupportedCodes(): array
    {
        return array_keys(self::getCurrencies());
    }

    public static function getActiveCodes(): array
    {
        // All currencies in the map are considered active
        return self::getSupportedCodes();
    }

    public static function getCurrencyInfo(string $code): ?array
    {
        return self::getCurrencies()[$code] ?? null;
    }

    public static function isValidCode(string $code): bool
    {
        return isset(self::getCurrencies()[$code]);
    }

    public static function getName(string $code): ?string
    {
        return self::getCurrencies()[$code]['name'] ?? null;
    }

    public static function getSymbol(string $code): ?string
    {
        return self::getCurrencies()[$code]['symbol'] ?? null;
    }
}