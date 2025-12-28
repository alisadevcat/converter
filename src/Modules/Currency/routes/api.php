<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Currency\Controllers\Admin\ExchangeRateController;

Route::prefix('api')
    ->group(function () {
        ///exchange-rates/sync-daily - post
    });