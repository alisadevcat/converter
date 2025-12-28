<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Currency\Controllers\Admin\ExchangeRateController;

Route::prefix('api')
    ->group(function () {
        Route::post('/exchange-rates/sync-daily', [ExchangeRateController::class, 'syncDailyRates'])->name('exchange-rates.sync-daily');
    });