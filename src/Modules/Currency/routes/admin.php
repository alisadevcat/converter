<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Controllers\Admin\ExchangeRateController;

Route::middleware(['web', 'auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/exchange-rates', [ExchangeRateController::class, 'index'])
            ->name('exchange-rates.index');
        Route::get('/exchange-rates/{baseCurrency}', [ExchangeRateController::class, 'show'])
            ->name('exchange-rates.show')
            ->where('baseCurrency', '[A-Z]{3}');
    });
