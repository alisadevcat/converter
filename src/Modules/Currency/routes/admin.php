<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Currency\Controllers\Admin\ExchangeRateController;
use App\Modules\Currency\Controllers\CurrencyConverterController;

Route::middleware(['web', 'auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
        Route::get('/exchange-rates/{baseCurrency}', [ExchangeRateController::class, 'show'])->name('exchange-rates.show');
        Route::post('/converter', [CurrencyConverterController::class, 'convert'])->name('converter.convert');
    });
